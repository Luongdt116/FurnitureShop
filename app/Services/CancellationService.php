<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancellationService
{
    public function cancelByCustomer(int $orderId, User $customer, string $reason): bool
    {
        return $this->cancel($orderId, 'customer', $customer, $reason);
    }

    public function cancelByAdmin(int $orderId, User $admin, string $reason): bool
    {
        abort_unless($admin->isAdmin(), 403);

        return $this->cancel($orderId, 'admin', $admin, $reason);
    }

    public function expireOnlinePayment(int $orderId): bool
    {
        return $this->cancel($orderId, 'system', null, 'Quá thời gian thanh toán');
    }

    /**
     * Use from another transaction after the caller has locked the order first,
     * then all of its payment rows. This is also used by payment callbacks that
     * arrive after the deadline but before the scheduler runs.
     */
    public function expireLockedOnlineOrder(Order $order, Collection $payments): bool
    {
        return $this->cancelLocked($order, $payments, 'system', null, 'Quá thời gian thanh toán', now());
    }

    private function cancel(int $orderId, string $actorType, ?User $actor, string $reason): bool
    {
        return DB::transaction(function () use ($orderId, $actorType, $actor, $reason): bool {
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->firstOrFail();
            if ($actorType === 'customer' && $order->user_id !== $actor?->id) {
                abort(404);
            }

            $payments = Payment::query()
                ->where('order_id', $order->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            return $this->cancelLocked($order, $payments, $actorType, $actor, $reason, now());
        }, 3);
    }

    private function cancelLocked(
        Order $order,
        Collection $payments,
        string $actorType,
        ?User $actor,
        string $reason,
        \Illuminate\Support\Carbon $now
    ): bool {
        if (in_array($order->status, ['cancelled', 'shipping', 'delivered'], true)) {
            return false;
        }

        if (!in_array($order->status, ['preparing', 'awaiting_shipment'], true)) {
            return false;
        }

        if ($actorType === 'system') {
            $onlineMethods = ['online_demo', 'vnpay', 'vietqr'];
            $unpaidStatuses = ['pending', 'failed', 'expired'];
            $hasPaidPayment = $payments->contains(fn (Payment $payment) => in_array($payment->status, ['paid', 'completed'], true));

            if (!in_array($order->payment_method, $onlineMethods, true)
                || !in_array($order->payment_status, $unpaidStatuses, true)
                || $hasPaidPayment
                || !$order->payment_expires_at
                || $order->payment_expires_at->isFuture()) {
                return false;
            }
        }

        $orderItems = OrderItem::query()
            ->where('order_id', $order->id)
            ->orderBy('product_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $orderItemIds = $orderItems->pluck('id');
        $deductedMovements = ProductStockMovement::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->where('movement_type', 'order_deducted')
            ->lockForUpdate()
            ->get()
            ->keyBy('order_item_id');
        $restockedIds = ProductStockMovement::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->where('movement_type', 'order_cancelled_restocked')
            ->lockForUpdate()
            ->pluck('order_item_id')
            ->all();
        $restockedLookup = array_fill_keys($restockedIds, true);

        $itemsToRestock = $orderItems->filter(fn (OrderItem $item) =>
            $deductedMovements->has($item->id) && !isset($restockedLookup[$item->id])
        );
        $productIds = $itemsToRestock->pluck('product_id')->unique()->sort()->values();
        $products = Product::withTrashed()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($itemsToRestock as $item) {
            $product = $products->get($item->product_id);
            if (!$product) {
                throw ValidationException::withMessages(['order' => 'Không thể hoàn kho vì thiếu sản phẩm trong đơn hàng.']);
            }

            $product->increment('stock', $item->quantity);
            ProductStockMovement::query()->create([
                'product_id' => $item->product_id,
                'order_item_id' => $item->id,
                'movement_type' => 'order_cancelled_restocked',
                'direction' => 'in',
                'quantity' => $item->quantity,
                'actor_user_id' => $actor?->id,
                'note' => "Hoàn kho cho đơn {$order->order_code}",
            ]);
        }

        $wasPaid = in_array($order->payment_status, ['paid', 'completed'], true)
            || $payments->contains(fn (Payment $payment) => in_array($payment->status, ['paid', 'completed'], true));
        $nextPaymentStatus = $actorType === 'system'
            ? 'expired'
            : ($wasPaid ? 'refund_pending' : 'cancelled');

        foreach ($payments as $payment) {
            if (in_array($payment->status, ['paid', 'completed'], true)) {
                $payment->status = 'refund_pending';
            } else {
                $payment->status = $nextPaymentStatus;
            }
            $payment->save();
        }

        $fromStatus = $order->status;
        $order->status = 'cancelled';
        $order->cancelled_by = $actorType;
        $order->cancel_reason = mb_substr($reason, 0, 255);
        $order->cancelled_at = $now;
        $order->payment_status = $wasPaid && $actorType !== 'system' ? 'refund_pending' : $nextPaymentStatus;
        $order->save();

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => $fromStatus,
            'to_status' => 'cancelled',
            'changed_by' => $actor?->id,
            'actor_type' => $actorType,
            'reason' => mb_substr($reason, 0, 255),
        ]);

        return true;
    }
}
