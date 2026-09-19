<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Notifications\CustomerOrderStatusUpdated;
use App\Support\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OnlineOrderWorkflowController extends Controller
{
    private array $statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled'];

    public function cashierIndex(): View
    {
        return view('cashier.online-orders.index', [
            'orders' => $this->onlineOrders(),
            'statusLabels' => $this->statusLabels(),
            'canOverride' => false,
        ]);
    }

    public function adminIndex(): View
    {
        return view('pos_admin.online-orders.index', [
            'orders' => $this->onlineOrders(),
            'statusLabels' => $this->statusLabels(),
            'canOverride' => $this->canOverride(auth()->user()),
        ]);
    }

    public function accept(Request $request, int $orderId): RedirectResponse
    {
        $user = $request->user();
        $order = $this->orderForUser($orderId, $user);

        if (! $order) {
            return back()->with('error', 'Order not found for this branch.');
        }

        if (! empty($order->assigned_staff_user_id)) {
            return back()->with('error', 'This order is already assigned to '.$order->assigned_staff_name.'.');
        }

        $updated = DB::transaction(function () use ($orderId, $user) {
            $payload = [
                'assigned_staff_user_id' => $user->id,
                'assigned_staff_name' => $user->name,
                'status' => 'confirmed',
                'delivery_status' => 'confirmed',
                'confirmed_at' => now(),
                'status_updated_by' => $user->id,
                'status_updated_by_name' => $user->name,
                'updated_at' => now(),
            ];
            $orderPayload = $payload;
            unset($orderPayload['status']);

            $updatedLines = OrderProduct::where('order_id', $orderId)
                ->whereNull('assigned_staff_user_id')
                ->update($payload);

            if (! $updatedLines) {
                return 0;
            }

            Order::whereKey($orderId)->whereNull('assigned_staff_user_id')->update($orderPayload);

            return $updatedLines;
        });

        if (! $updated) {
            return back()->with('error', 'Another staff member accepted this order first.');
        }

        $this->notifyCustomer($orderId, 'confirmed', $user->name);
        $this->notifyBranchAdmins($orderId, $user->name, 'accepted');
        $this->markOrderNotificationsRead($user, $orderId);

        return back()->with('success', 'Order accepted and assigned to you.');
    }

    public function updateStatus(Request $request, int $orderId): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', $this->statuses)],
        ]);

        $user = $request->user();
        $order = $this->orderForUser($orderId, $user);

        if (! $order) {
            return back()->with('error', 'Order not found for this branch.');
        }

        if (! $this->canProcess($order, $user)) {
            return back()->with('error', 'This order is assigned to '.$order->assigned_staff_name.'.');
        }

        $status = $data['status'];
        $timestampColumn = match ($status) {
            'confirmed' => 'confirmed_at',
            'preparing' => 'preparing_at',
            'out_for_delivery' => 'out_for_delivery_at',
            'delivered', 'completed' => 'delivered_at',
            'cancelled' => 'cancelled_at',
            default => null,
        };

        $payload = [
            'status' => $status,
            'delivery_status' => $status,
            'status_updated_by' => $user->id,
            'status_updated_by_name' => $user->name,
            'updated_at' => now(),
        ];

        if ($timestampColumn) {
            $payload[$timestampColumn] = now();
        }
        $orderPayload = $payload;
        unset($orderPayload['status']);

        DB::transaction(function () use ($orderId, $payload, $orderPayload) {
            OrderProduct::where('order_id', $orderId)->update($payload);
            Order::whereKey($orderId)->update($orderPayload);
        });

        $this->notifyCustomer($orderId, $status, $user->name);

        if (in_array($status, ['out_for_delivery', 'delivered', 'completed', 'cancelled'], true)) {
            $this->notifyBranchAdmins($orderId, $user->name, $status);
        }
        $this->markOrderNotificationsRead($user, $orderId);

        return back()->with('success', 'Order status updated to '.$this->statusLabels()[$status].'.');
    }

    private function onlineOrders()
    {
        $branchId = BranchContext::activeId();

        return DB::table('order_products as lines')
            ->leftJoin('branches', 'branches.id', '=', 'lines.branch_id')
            ->leftJoin('users as assigned', 'assigned.id', '=', 'lines.assigned_staff_user_id')
            ->when($branchId, fn ($query) => $query->where('lines.branch_id', $branchId))
            ->selectRaw('
                lines.order_id,
                lines.branch_id,
                MAX(branches.name) as branch_name,
                MAX(lines.username) as username,
                MAX(lines.phone) as phone,
                MAX(lines.address) as address,
                MAX(lines.fulfillment_method) as fulfillment_method,
                MAX(lines.delivery_status) as delivery_status,
                MAX(lines.status) as status,
                MAX(lines.delivery_fee) as delivery_fee,
                MAX(lines.delivery_distance_km) as delivery_distance_km,
                MAX(lines.delivery_latitude) as delivery_latitude,
                MAX(lines.delivery_longitude) as delivery_longitude,
                MAX(lines.assigned_staff_user_id) as assigned_staff_user_id,
                MAX(lines.assigned_staff_name) as assigned_staff_name,
                MAX(lines.status_updated_by_name) as status_updated_by_name,
                MIN(lines.confirmed_at) as confirmed_at,
                MIN(lines.preparing_at) as preparing_at,
                MIN(lines.out_for_delivery_at) as out_for_delivery_at,
                MIN(lines.delivered_at) as delivered_at,
                MIN(lines.cancelled_at) as cancelled_at,
                SUM(lines.quantity) as item_count,
                SUM(lines.subtotal) as item_total,
                MAX(lines.created_at) as created_at
            ')
            ->groupBy('lines.order_id', 'lines.branch_id')
            ->orderByDesc('created_at')
            ->get();
    }

    private function orderForUser(int $orderId, ?User $user)
    {
        if (! $user) {
            return null;
        }

        $branchId = BranchContext::activeId();
        $query = DB::table('order_products')->where('order_id', $orderId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if (! $this->canOverride($user) && $branchId === null) {
            return null;
        }

        return $query
            ->selectRaw('
                order_id,
                branch_id,
                MAX(user_id) as customer_user_id,
                MAX(fulfillment_method) as fulfillment_method,
                MAX(assigned_staff_user_id) as assigned_staff_user_id,
                MAX(assigned_staff_name) as assigned_staff_name
            ')
            ->groupBy('order_id', 'branch_id')
            ->first();
    }

    private function canProcess(object $order, User $user): bool
    {
        if ($this->canOverride($user)) {
            return true;
        }

        return (int) ($order->assigned_staff_user_id ?? 0) === (int) $user->id;
    }

    private function canOverride(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array((int) ($user->is_admin ?? 0), [1, 2], true)
            || in_array(($user->role ?? null), ['admin', 'superadmin'], true);
    }

    private function notifyCustomer(int $orderId, string $status, string $processorName): void
    {
        try {
            $line = OrderProduct::where('order_id', $orderId)->first();
            $customer = $line ? User::find($line->user_id) : null;

            if ($customer) {
                $customer->notify(new CustomerOrderStatusUpdated(
                    $orderId,
                    $status,
                    $line->fulfillment_method ?? 'delivery',
                    $processorName
                ));
            }
        } catch (\Exception $e) {
            Log::error('Customer order status notification failed', [
                'order_id' => $orderId,
                'status' => $status,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function notifyBranchAdmins(int $orderId, string $processorName, string $event): void
    {
        try {
            $line = OrderProduct::where('order_id', $orderId)->first();
            if (! $line || ! $line->branch_id) {
                return;
            }

            $admins = User::where('role', 'branch_admin')
                ->whereHas('branches', fn ($branches) => $branches->where('branches.id', $line->branch_id))
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new CustomerOrderStatusUpdated(
                    $orderId,
                    $event,
                    $line->fulfillment_method ?? 'delivery',
                    $processorName,
                    ['database']
                ));
            }
        } catch (\Exception $e) {
            Log::error('Branch admin order processing notification failed', [
                'order_id' => $orderId,
                'event' => $event,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function markOrderNotificationsRead(User $user, int $orderId): void
    {
        $user->unreadNotifications()
            ->where(function ($query) use ($orderId) {
                $query->where('data->order_id', $orderId)
                    ->orWhere('data', 'like', '%"order_id":'.$orderId.'%')
                    ->orWhere('data', 'like', '%"order_id":"'.$orderId.'"%');
            })
            ->update(['read_at' => now()]);
    }

    private function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }
}
