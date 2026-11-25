<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancellationService
{
    public const PAYMENT_TIMEOUT_REASON = 'Quá thời gian thanh toán';

    private const ONLINE_METHODS = ['online_demo', 'vnpay', 'vietqr'];

    public function cancel(Order $order, string $reason, ?User $actor, string $actorType): Order
    {
        $reason = trim($reason);

        if (! in_array($actorType, ['customer', 'admin', 'system'], true)) {
            throw ValidationException::withMessages([
                'actor' => 'Tác nhân hủy đơn không hợp lệ.',
            ]);
        }

        if ($actorType === 'system') {
            $reason = self::PAYMENT_TIMEOUT_REASON;
        }

        if ($reason === '' || mb_strlen($reason) > 255) {
            throw ValidationException::withMessages([
                'reason' => 'Lý do hủy là bắt buộc và không được vượt quá 255 ký tự.',
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $actor, $actorType): Order {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateActor($lockedOrder, $actor, $actorType);

            if ($lockedOrder->status === 'cancelled') {
                return $lockedOrder;
            }

            $allowedStatuses = match ($actorType) {
                'customer' => ['preparing', 'awaiting_shipment'],
                'admin' => ['preparing', 'awaiting_shipment', 'shipping'],
                'system' => ['preparing', 'awaiting_shipment'],
            };

            if (! in_array($lockedOrder->status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'order' => 'Đơn hàng không còn ở trạng thái có thể hủy.',
                ]);
            }

            $pendingPayments = $lockedOrder->payments()
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($actorType === 'system') {
                $hasExpiredPendingPayment = $pendingPayments->contains(fn ($payment) =>
                    in_array($payment->method, self::ONLINE_METHODS, true)
                    && $payment->payment_expires_at !== null
                    && $payment->payment_expires_at->lte(now())
                );

                if (! in_array($lockedOrder->payment_method, self::ONLINE_METHODS, true)
                    || $lockedOrder->payment_status !== 'pending'
                    || ! $hasExpiredPendingPayment) {
                    throw ValidationException::withMessages([
                        'order' => 'Đơn hàng chưa quá hạn thanh toán trực tuyến.',
                    ]);
                }
            }

            $fromStatus = $lockedOrder->status;
            $lockedOrder->status = 'cancelled';
            $lockedOrder->cancel_reason = $reason;
            $lockedOrder->cancelled_by = $actorType;
            $lockedOrder->cancelled_at = now();

            if ($lockedOrder->payment_status !== 'paid') {
                $lockedOrder->payment_status = 'cancelled';
            }

            $lockedOrder->save();

            foreach ($pendingPayments as $payment) {
                $payment->status = 'cancelled';
                $payment->save();
            }

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'changed_by' => $actor?->id,
                'actor_type' => $actorType,
                'reason' => $reason,
            ]);

            // Inventory adjustments are intentionally left to the inventory owner/service.
            // This service never changes product stock or inserts stock movement rows.

            return $lockedOrder->refresh()->load(['payments', 'statusHistories']);
        });
    }

    public function cancelExpiredOnlinePayments(): int
    {
        $orderIds = Order::query()
            ->whereIn('status', ['preparing', 'awaiting_shipment'])
            ->whereIn('payment_method', self::ONLINE_METHODS)
            ->where('payment_status', 'pending')
            ->whereHas('payments', fn ($query) => $query
                ->where('status', 'pending')
                ->whereIn('method', self::ONLINE_METHODS)
                ->whereNotNull('payment_expires_at')
                ->where('payment_expires_at', '<=', now()))
            ->orderBy('id')
            ->pluck('id');

        $cancelledCount = 0;

        foreach ($orderIds as $orderId) {
            $order = Order::query()->find($orderId);
            if (! $order) {
                continue;
            }

            try {
                $this->cancel($order, self::PAYMENT_TIMEOUT_REASON, null, 'system');
                $cancelledCount++;
            } catch (ValidationException) {
                // Another worker or a payment callback may have changed the order after the scan.
            }
        }

        return $cancelledCount;
    }

    private function validateActor(Order $order, ?User $actor, string $actorType): void
    {
        if ($actorType === 'system') {
            if ($actor !== null) {
                throw ValidationException::withMessages([
                    'actor' => 'Tác vụ hệ thống không nhận người dùng thay mặt.',
                ]);
            }

            return;
        }

        if (! $actor) {
            throw ValidationException::withMessages([
                'actor' => 'Cần có tài khoản thực hiện hủy đơn.',
            ]);
        }

        if ($actorType === 'customer' && (int) $actor->id !== (int) $order->user_id) {
            throw ValidationException::withMessages([
                'order' => 'Bạn không có quyền hủy đơn hàng này.',
            ]);
        }

        if ($actorType === 'admin' && (! $actor->isAdmin() || ! $actor->is_active)) {
            throw ValidationException::withMessages([
                'actor' => 'Tài khoản không có quyền hủy đơn hàng với vai trò quản trị.',
            ]);
        }
    }
}
