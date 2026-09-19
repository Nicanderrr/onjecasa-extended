<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderProduct; // Using OrderProduct only
use App\Models\Cart;
use App\Models\User;
use App\Support\MealExtras;
use App\Support\ProductOptions;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OnlineOrderAssignedToBranch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    public function confirmOrder(Request $request)
    {
        // Ensure the user is logged in
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'You need to log in to confirm the order.');
        }

        // Validate the request
        $request->validate([
            'phone' => 'required|string',
            'branch_id' => 'required|integer|exists:branches,id',
            'fulfillment_method' => 'required|in:delivery,pickup',
            'address' => 'required_if:fulfillment_method,delivery|nullable|string',
            'delivery_latitude' => 'required_if:fulfillment_method,delivery|nullable|numeric|between:-90,90',
            'delivery_longitude' => 'required_if:fulfillment_method,delivery|nullable|numeric|between:-180,180',
            'payment_method' => 'required|in:paystack',
            'payment_channel' => 'required|in:card,mobile_money,bank_transfer',
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone);
        $fulfillmentMethod = $request->input('fulfillment_method', 'delivery');
        $address = $fulfillmentMethod === 'pickup'
            ? 'Pickup at store'
            : (string) $request->address;
        try {
            $preparedItems = $this->getPreparedCartItems($user->id);
            $branchId = (int) $request->input('branch_id');
            $branch = Branch::whereKey($branchId)->where('is_active', true)->firstOrFail();
            $deliveryData = $this->deliveryData($request, $branch);

            if (empty($preparedItems)) {
                return redirect()->back()->with('error', 'Your cart is empty.');
            }

            if ($request->payment_method === 'paystack') {
                return $this->initializePaystack($request, $user, $preparedItems, $branch, $deliveryData);
            }

            $this->createOrderFromItems(
                $user,
                $preparedItems,
                $normalizedPhone,
                $address,
                'pending',
                'cod',
                $fulfillmentMethod,
                $branchId,
                $deliveryData
            );

            return redirect()->route('order.history')->with('message', 'Order confirmed successfully!');

        } catch (\Exception $e) {
            Log::error('Checkout confirmOrder failed', [
                'user_id' => $user->id ?? null,
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Checkout failed: '.$e->getMessage());
        }
    }

    public function paystackCallback(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to complete payment.');
        }

        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('checkout')->with('error', 'Missing Paystack payment reference.');
        }

        $pendingCheckout = session('pending_checkout');
        if (
            !$pendingCheckout ||
            ($pendingCheckout['reference'] ?? null) !== $reference ||
            (int) ($pendingCheckout['user_id'] ?? 0) !== (int) $user->id
        ) {
            return redirect()->route('checkout')->with('error', 'Unable to find pending checkout for this payment.');
        }

        $secretKey = config('services.paystack.secret_key');
        if (!$secretKey) {
            return redirect()->route('checkout')->with('error', 'Paystack is not configured. Add PAYSTACK_SECRET_KEY.');
        }

        try {
            $verifyResponse = Http::withToken($secretKey)
                ->acceptJson()
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if (!$verifyResponse->ok()) {
                return redirect()->route('checkout')->with('error', 'Unable to verify payment with Paystack.');
            }

            $verification = $verifyResponse->json();
            $isSuccess = ($verification['status'] ?? false) === true
                && ($verification['data']['status'] ?? null) === 'success';

            if (!$isSuccess) {
                $paymentStatus = $verification['data']['status'] ?? 'failed';
                return redirect()->route('checkout')->with('error', "Payment not successful (status: {$paymentStatus}).");
            }

            $this->createOrderFromItems(
                $user,
                $pendingCheckout['items'],
                $this->normalizePhone($pendingCheckout['phone']),
                $pendingCheckout['address'],
                'paid',
                'paystack',
                $pendingCheckout['fulfillment_method'] ?? 'delivery'
                ,
                (int) ($pendingCheckout['branch_id'] ?? 0),
                $pendingCheckout['delivery'] ?? []
            );

            session()->forget('pending_checkout');

            return redirect()->route('order.history')->with('message', 'Payment successful. Order placed!');
        } catch (\Exception $e) {
            Log::error('Paystack callback failed', [
                'user_id' => $user->id ?? null,
                'reference' => $reference,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('checkout')->with('error', 'Paystack verification failed: '.$e->getMessage());
        }
    }

    private function initializePaystack(Request $request, $user, array $preparedItems, Branch $branch, array $deliveryData)
    {
        $secretKey = config('services.paystack.secret_key');
        if (!$secretKey) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Paystack is not configured. Add PAYSTACK_SECRET_KEY in your .env.');
        }

        $orderTotal = collect($preparedItems)->sum('subtotal');
        $grandTotal = $orderTotal + (float) ($deliveryData['delivery_fee'] ?? 0);
        $reference = 'rrlc_' . uniqid();
        $paymentChannel = $request->input('payment_channel', 'card');

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => (int) round($grandTotal * 100),
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'channels' => [$paymentChannel],
                'metadata' => [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'phone' => $request->phone,
                    'address' => $deliveryData['address'],
                    'fulfillment_method' => $request->input('fulfillment_method', 'delivery'),
                    'payment_channel' => $paymentChannel,
                    'delivery_fee' => $deliveryData['delivery_fee'],
                    'delivery_distance_km' => $deliveryData['delivery_distance_km'],
                ],
            ]);

        if (!$response->ok() || ($response->json('status') !== true)) {
            $message = $response->json('message', 'Paystack initialization failed.');
            return redirect()->back()
                ->withInput()
                ->with('error', $message);
        }

        session([
            'pending_checkout' => [
                'reference' => $reference,
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'phone' => $request->phone,
                'address' => $deliveryData['address'],
                'fulfillment_method' => $request->input('fulfillment_method', 'delivery'),
                'payment_channel' => $paymentChannel,
                'delivery' => $deliveryData,
                'items' => $preparedItems,
            ],
        ]);

        $checkoutUrl = $response->json('data.authorization_url');

        return redirect()->away($checkoutUrl);
    }

    private function isValidInternationalPhone(string $phone): bool
    {
        return (bool) preg_match('/^\+\d{10,15}$/', $phone);
    }

    private function normalizePhone(string $phone): string
    {
        $raw = trim($phone);
        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($hasPlus) {
            return '+' . $digits;
        }

        // Ghana defaults: 0XXXXXXXXX or 233XXXXXXXXX -> +233XXXXXXXXX
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return '+233' . substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '233')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    private function getPreparedCartItems(int $userId): array
    {
        $cartItems = Cart::where('user_id', $userId)
            ->with('product')
            ->get();

        $preparedItems = [];

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            if (!$product) {
                continue;
            }

            $quantity = (int) $cartItem->quantity;
            $unitPrice = ProductOptions::cartBasePrice($cartItem) + MealExtras::total($cartItem->color);
            $lineSubtotal = $unitPrice * $quantity;

            $preparedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $unitPrice,
                'quantity' => $quantity,
                'size' => $cartItem->size,
                'color' => $cartItem->color,
                'subtotal' => $lineSubtotal,
            ];
        }

        return $preparedItems;
    }

    private function deliveryData(Request $request, Branch $branch): array
    {
        $fulfillmentMethod = $request->input('fulfillment_method', 'delivery');

        if ($fulfillmentMethod === 'pickup') {
            return [
                'address' => 'Pickup at '.$branch->name.($branch->address ? ' - '.$branch->address : ''),
                'delivery_latitude' => null,
                'delivery_longitude' => null,
                'delivery_distance_km' => 0,
                'delivery_fee' => 0,
                'delivery_status' => 'pending',
            ];
        }

        if ($branch->latitude === null || $branch->longitude === null) {
            throw new \RuntimeException('The selected branch needs map coordinates before delivery can be calculated.');
        }

        $lat = (float) $request->input('delivery_latitude');
        $lng = (float) $request->input('delivery_longitude');
        $distance = $this->distanceKm((float) $branch->latitude, (float) $branch->longitude, $lat, $lng);
        $maxDistance = $branch->max_delivery_distance_km !== null ? (float) $branch->max_delivery_distance_km : null;

        if ($maxDistance !== null && $distance > $maxDistance) {
            throw new \RuntimeException('This delivery location is outside the selected branch delivery range.');
        }

        return [
            'address' => (string) $request->input('address'),
            'delivery_latitude' => $lat,
            'delivery_longitude' => $lng,
            'delivery_distance_km' => round($distance, 2),
            'delivery_fee' => round((float) $branch->base_delivery_fee + ($distance * (float) $branch->delivery_fee_per_km), 2),
            'delivery_status' => 'pending',
        ];
    }

    private function distanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function createOrderFromItems($user, array $items, string $phone, string $address, string $status, string $paymentMethod = 'cod', string $fulfillmentMethod = 'delivery', int $branchId = 0, array $deliveryData = [])
    {
        DB::beginTransaction();

        try {
            $itemsTotal = collect($items)->sum('subtotal');
            $deliveryFee = (float) ($deliveryData['delivery_fee'] ?? 0);
            $orderTotal = $itemsTotal + $deliveryFee;
            $deliveryStatus = $deliveryData['delivery_status'] ?? 'pending';

            $order = Order::create([
                'branch_id' => $branchId ?: null,
                'user_id' => $user->id,
                'total' => $orderTotal,
                'fulfillment_method' => $fulfillmentMethod,
                'delivery_address' => $deliveryData['address'] ?? $address,
                'delivery_latitude' => $deliveryData['delivery_latitude'] ?? null,
                'delivery_longitude' => $deliveryData['delivery_longitude'] ?? null,
                'delivery_distance_km' => $deliveryData['delivery_distance_km'] ?? 0,
                'delivery_fee' => $deliveryFee,
                'delivery_status' => $deliveryStatus,
                'assigned_staff_user_id' => null,
                'assigned_staff_name' => null,
                'status_updated_by' => null,
                'status_updated_by_name' => null,
            ]);

            foreach ($items as $item) {
                OrderProduct::create([
                    'branch_id' => $branchId ?: null,
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['subtotal'],
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'subtotal' => $item['subtotal'],
                    'username' => $user->name,
                    'phone' => $phone,
                    'address' => $deliveryData['address'] ?? $address,
                    'fulfillment_method' => $fulfillmentMethod,
                    'delivery_latitude' => $deliveryData['delivery_latitude'] ?? null,
                    'delivery_longitude' => $deliveryData['delivery_longitude'] ?? null,
                    'delivery_distance_km' => $deliveryData['delivery_distance_km'] ?? 0,
                    'delivery_fee' => $deliveryFee,
                    'delivery_status' => $deliveryStatus,
                    'assigned_staff_user_id' => null,
                    'assigned_staff_name' => null,
                    'status_updated_by' => null,
                    'status_updated_by_name' => null,
                    'status' => $status,
                ]);
            }

            // Clear the user's cart after successful order creation.
            $productController = new ProductController();
            $productController->clearCart($user->id);

            DB::commit();

            $this->notifyAdminsOfNewOrder(
                (int) $order->id,
                (string) $user->name,
                (int) collect($items)->sum('quantity'),
                (float) $orderTotal,
                $paymentMethod,
                $branchId
            );
            $this->notifyBranchWorkersOfNewOrder(
                (int) $order->id,
                (string) $user->name,
                (int) collect($items)->sum('quantity'),
                (float) $orderTotal,
                $fulfillmentMethod,
                $branchId
            );

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function notifyBranchWorkersOfNewOrder(
        int $orderId,
        string $customerName,
        int $itemCount,
        float $orderTotal,
        string $fulfillmentMethod,
        int $branchId
    ): void {
        try {
            $branch = Branch::find($branchId);
            $workers = User::whereIn('role', ['cashier', 'branch_admin'])
                ->where('is_active', true)
                ->whereHas('branches', fn ($branches) => $branches->where('branches.id', $branchId))
                ->get();

            foreach ($workers as $worker) {
                $worker->notify(new OnlineOrderAssignedToBranch(
                    $orderId,
                    $customerName,
                    $itemCount,
                    $orderTotal,
                    $fulfillmentMethod,
                    $branch?->name ?? 'Selected branch',
                    ['database']
                ));
            }

            foreach ($workers->where('email_notifications_enabled', true)->whereNotNull('email') as $worker) {
                try {
                    $worker->notify(new OnlineOrderAssignedToBranch(
                        $orderId,
                        $customerName,
                        $itemCount,
                        $orderTotal,
                        $fulfillmentMethod,
                        $branch?->name ?? 'Selected branch',
                        ['mail']
                    ));
                } catch (\Exception $mailException) {
                    Log::error('Branch worker order email failed', [
                        'order_id' => $orderId,
                        'worker_id' => $worker->id,
                        'email' => $worker->email,
                        'message' => $mailException->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Branch worker order notification failed', [
                'order_id' => $orderId,
                'branch_id' => $branchId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function notifyAdminsOfNewOrder(
        int $orderId,
        string $customerName,
        int $itemCount,
        float $orderTotal,
        string $paymentMethod,
        int $branchId
    ): void {
        try {
            $admins = User::where(function ($query) use ($branchId) {
                $query->whereIn('is_admin', [1, 2])
                    ->orWhere(function ($branchAdmins) use ($branchId) {
                        $branchAdmins->where('role', 'branch_admin')
                            ->whereHas('branches', fn ($branches) => $branches->where('branches.id', $branchId));
                    });
            })
                ->whereNotNull('email')
                ->get();

            foreach ($admins as $admin) {
                // Always persist admin-panel notification in database.
                $admin->notify(new NewOrderPlaced(
                    $orderId,
                    $customerName,
                    $itemCount,
                    $orderTotal,
                    $paymentMethod,
                    ['database']
                ));
            }

            // Send email notifications separately so SMTP issues do not block DB notifications.
            $emailTargets = $admins->pluck('email')->filter()->values()->all();
            $targetEmail = env('ADMIN_ORDER_NOTIFICATION_EMAIL');
            if ($targetEmail) {
                $emailTargets[] = $targetEmail;
            }
            $emailTargets = array_unique($emailTargets);

            foreach ($emailTargets as $email) {
                try {
                    Notification::route('mail', $email)->notify(new NewOrderPlaced(
                        $orderId,
                        $customerName,
                        $itemCount,
                        $orderTotal,
                        $paymentMethod,
                        ['mail']
                    ));
                } catch (\Exception $mailException) {
                    Log::error('Admin order email failed', [
                        'order_id' => $orderId,
                        'email' => $email,
                        'message' => $mailException->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Admin order notification failed', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function viewOrderHistory()
    {
        $user = Auth::user();

        // Get all unique order IDs for this user
        $orderIds = OrderProduct::where('user_id', $user->id)
            ->distinct()
            ->pluck('order_id');

        $orders = [];

        foreach ($orderIds as $orderId) {
            // Get all products in this order
            $orderProducts = OrderProduct::where('order_id', $orderId)
                ->with('product')
                ->get();
            
            // Calculate order total
            $orderHeader = Order::find($orderId);
            $orderTotal = $orderHeader?->total ?? ($orderProducts->sum('subtotal') + (float) ($orderProducts->first()->delivery_fee ?? 0));
            
            // Create an order object
            $order = (object)[
                'id' => $orderId,
                'orderProducts' => $orderProducts,
                'total' => $orderTotal,
                'status' => $orderProducts->first()->status ?? 'pending',
                'created_at' => $orderProducts->first()->created_at,
                'phone' => $orderProducts->first()->phone,
                'address' => $orderProducts->first()->address,
                'fulfillment_method' => $orderProducts->first()->fulfillment_method ?? 'delivery',
                'branch' => $orderProducts->first()->branch,
                'delivery_fee' => $orderProducts->first()->delivery_fee ?? 0,
                'delivery_distance_km' => $orderProducts->first()->delivery_distance_km ?? 0,
                'delivery_status' => $orderProducts->first()->delivery_status ?? $orderProducts->first()->status ?? 'pending',
            ];
            
            $orders[] = $order;
        }

        // Sort orders by created_at descending
        usort($orders, function($a, $b) {
            return $b->created_at <=> $a->created_at;
        });

        // Calculate stats from the user's orders only
        $totalOrders = count($orders);
        $totalSpent = collect($orders)->sum('total');
        
        // Calculate total items purchased
        $totalItems = 0;
        foreach ($orders as $order) {
            $totalItems += $order->orderProducts->sum('quantity');
        }
        
        $avgOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        return view('Frontend.order_history', compact('orders', 'totalOrders', 'totalSpent', 'totalItems', 'avgOrderValue'));
    }

    public function showOrderDetails($orderId)
    {
        $user = Auth::user();
        
        $orderProducts = OrderProduct::where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($orderProducts->isEmpty()) {
            abort(404);
        }

        $order = (object)[
            'id' => $orderId,
            'orderProducts' => $orderProducts,
            'total' => $orderProducts->sum('subtotal'),
            'status' => $orderProducts->first()->status ?? 'pending',
            'created_at' => $orderProducts->first()->created_at,
            'phone' => $orderProducts->first()->phone,
            'address' => $orderProducts->first()->address,
            'fulfillment_method' => $orderProducts->first()->fulfillment_method ?? 'delivery',
            'branch' => $orderProducts->first()->branch,
            'delivery_fee' => $orderProducts->first()->delivery_fee ?? 0,
            'delivery_distance_km' => $orderProducts->first()->delivery_distance_km ?? 0,
            'delivery_status' => $orderProducts->first()->delivery_status ?? $orderProducts->first()->status ?? 'pending',
        ];

        return view('Frontend.order_history', [
            'orders' => [$order],
            'totalOrders' => 1,
            'totalSpent' => $order->total,
            'totalItems' => $order->orderProducts->sum('quantity'),
            'avgOrderValue' => $order->total,
        ]);
    }

    public function cancelOrder($orderId)
    {
        $user = Auth::user();
        
        $orderProducts = OrderProduct::where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        if ($orderProducts->isNotEmpty()) {
            foreach ($orderProducts as $product) {
                $product->update(['status' => 'cancelled']);
            }
        }

        return redirect()->back()->with('message', 'Order cancelled successfully.');
    }

    public function reorder($orderId)
    {
        $user = Auth::user();
        
        // Here you would typically add the items back to the cart
        return redirect()->route('product_page')->with('message', 'Items added to your cart for reorder.');
    }
}


