<?php

namespace App\Http\Controllers;
use App\Models\OrderProduct;
use App\Models\Order; 
use App\Models\User; 
use App\Notifications\NewOrderPlaced;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AdminController extends Controller
{
    private array $deliveryStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled'];

    // Your existing methods...

    public function showNewOrders()
    {
        // Fetch orders from the order_product table
        $orders = OrderProduct::with('user')
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->get();

        $userIds = $orders->pluck('user_id')->unique(); // Get unique user IDs from orders
        $users = User::whereIn('id', $userIds)->get();  // Fetch users based on those IDs

        return view('backend.orders', compact('orders', 'users'));
    }

    // Delete Order
    public function DeleteOrder($id)
    {
        $deleteData = OrderProduct::find($id);
        $deleteData->delete();
        return redirect()->back();
    }

    public function showAllOrders()
    {
        // Fetch orders from the order_product table
        $orders = DB::table('order_products')
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->get();
        return view('backend.allOrders', compact('orders'));
    }

    public function dashboard()
    {
        // Calculate total sales
        $branchId = BranchContext::activeId();
        $totalSales = OrderProduct::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->sum(DB::raw('quantity * price'));

        // Find the most sold product
        $mostSoldProduct = OrderProduct::select('product_name', DB::raw('SUM(quantity) as total_sold'))
                                        ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                                        ->groupBy('product_name')
                                        ->orderByDesc('total_sold')
                                        ->first();
        $totalUsers = User::count();
        $newOrders = OrderProduct::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();
        return view('backend.dashboard', compact('totalSales', 'mostSoldProduct', 'totalUsers', 'newOrders'));
    }

    /**
     * NEW METHOD: Update individual order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,out_for_delivery,delivered,completed,cancelled'
        ]);
        
        $order = OrderProduct::query()
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->find($id);
        if ($order) {
            $order->status = $request->status;
            $order->delivery_status = $request->status;
            $order->save();
            Order::whereKey($order->order_id)->update(['delivery_status' => $request->status]);
            
            return redirect()->back()->with('success', 'Order status updated successfully');
        }
        
        return redirect()->back()->with('error', 'Order not found');
    }

    /**
     * NEW METHOD: Update status for all items in an order (by order_id)
     */
    public function updateOrderBatchStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,out_for_delivery,delivered,completed,cancelled'
        ]);
        
        // Update status for all items with this order_id
        $updated = OrderProduct::where('order_id', $orderId)
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->update(['status' => $request->status, 'delivery_status' => $request->status]);
        if ($updated) {
            Order::whereKey($orderId)->update(['delivery_status' => $request->status]);
        }
        
        if ($updated) {
            return redirect()->back()->with('success', 'Order status updated successfully');
        }
        
        return redirect()->back()->with('error', 'No orders found');
    }

    /**
     * NEW METHOD: Bulk update multiple orders
     */
    public function bulkUpdateOrderStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required',
            'status' => 'required|in:pending,confirmed,preparing,out_for_delivery,delivered,completed,cancelled'
        ]);
        
        $updated = 0;
        foreach ($request->order_ids as $orderId) {
            $result = OrderProduct::where('id', $orderId)
                ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                ->update(['status' => $request->status, 'delivery_status' => $request->status]);
            if ($result) {
                $line = OrderProduct::find($orderId);
                if ($line) {
                    Order::whereKey($line->order_id)->update(['delivery_status' => $request->status]);
                }
                $updated++;
            }
        }
        
        return redirect()->back()->with('success', "$updated orders status updated successfully");
    }

    public function markNotificationsRead(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }

    public function sendTestOrderEmail(Request $request)
    {
        if (!auth()->check() || !in_array((int) auth()->user()->is_admin, [1, 2], true)) {
            return redirect()->back()->with([
                'message' => 'Unauthorized',
                'alert-type' => 'error',
            ]);
        }

        $targetEmail = env('ADMIN_ORDER_NOTIFICATION_EMAIL');
        if (!$targetEmail) {
            return redirect()->back()->with([
                'message' => 'ADMIN_ORDER_NOTIFICATION_EMAIL is not set in .env',
                'alert-type' => 'error',
            ]);
        }

        if (!env('MAIL_PASSWORD')) {
            return redirect()->back()->with([
                'message' => 'MAIL_PASSWORD is empty. Set your Gmail App Password in .env to send emails.',
                'alert-type' => 'error',
            ]);
        }

        try {
            Notification::route('mail', $targetEmail)->notify(new NewOrderPlaced(
                999999,
                'Test Customer',
                2,
                150.00,
                'paystack',
                ['mail']
            ));

            return redirect()->back()->with([
                'message' => 'Test order email sent to ' . $targetEmail,
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }
}
