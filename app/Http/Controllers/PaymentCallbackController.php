<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Signature format: lowercase hex HMAC-SHA256 of
     * payment_id|transaction_code|status|amount formatted to two decimals.
     */
    public function __invoke(Request $request, CancellationService $cancellations): JsonResponse
    {
        $payload = $request->validate([
            'payment_id' => ['required', 'integer', 'min:1'],
            'transaction_code' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:paid,failed'],
            'amount' => ['required', 'numeric', 'min:0'],
            'signature' => ['required', 'string', 'size:64'],
        ]);

        $secret = (string) config('services.payment_callback.secret');
        if ($secret === '') {
            return response()->json(['message' => 'Payment callback is not configured.'], 503);
        }

        $canonical = implode('|', [
            $payload['payment_id'],
            $payload['transaction_code'],
            $payload['status'],
            number_format((float) $payload['amount'], 2, '.', ''),
        ]);
        $expected = hash_hmac('sha256', $canonical, $secret);
        if (!hash_equals($expected, strtolower($payload['signature']))) {
            return response()->json(['message' => 'Invalid payment callback signature.'], 401);
        }

        $result = DB::transaction(function () use ($payload, $cancellations): array {
            $candidate = Payment::query()->findOrFail($payload['payment_id']);
            $order = Order::query()->whereKey($candidate->order_id)->lockForUpdate()->firstOrFail();
            $payments = Payment::query()
                ->where('order_id', $order->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $payment = $payments->firstWhere('id', (int) $payload['payment_id']);

            abort_unless($payment, 404);
            abort_unless(in_array($order->payment_method, ['online_demo', 'vnpay', 'vietqr'], true), 422);
            abort_unless((int) round((float) $payment->amount * 100) === (int) round((float) $payload['amount'] * 100), 422);

            if (in_array($payment->status, ['paid', 'completed', 'refund_pending', 'refunded'], true)) {
                if ($payment->transaction_code !== $payload['transaction_code']) {
                    abort(409, 'Payment already has a different transaction code.');
                }

                return ['status' => $payment->status, 'late' => $order->status === 'cancelled'];
            }

            if ($order->status === 'cancelled') {
                if ($payload['status'] === 'paid') {
                    $payment->status = 'refund_pending';
                    $payment->transaction_code = $payload['transaction_code'];
                    $payment->paid_at = now();
                    $payment->save();
                    $order->payment_status = 'refund_pending';
                    $order->save();
                    Log::warning('Late payment received for cancelled order; reconciliation required.', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'transaction_code' => $payload['transaction_code'],
                    ]);
                }

                return ['status' => $payment->fresh()->status, 'late' => true];
            }

            if ($order->payment_expires_at && !$order->payment_expires_at->isFuture()) {
                $cancellations->expireLockedOnlineOrder($order, $payments);
                if ($payload['status'] === 'paid') {
                    $payment->status = 'refund_pending';
                    $payment->transaction_code = $payload['transaction_code'];
                    $payment->paid_at = now();
                    $payment->save();
                    $order->payment_status = 'refund_pending';
                    $order->save();
                    Log::warning('Payment arrived after its deadline; reconciliation required.', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'transaction_code' => $payload['transaction_code'],
                    ]);
                }

                return ['status' => $payment->fresh()->status, 'late' => true];
            }

            if ($payload['status'] === 'paid') {
                $payment->status = 'paid';
                $payment->transaction_code = $payload['transaction_code'];
                $payment->paid_at = now();
                $payment->save();
                $order->payment_status = 'paid';
                $order->save();
            } else {
                $payment->status = 'failed';
                $payment->transaction_code = $payload['transaction_code'];
                $payment->save();
                $order->payment_status = 'failed';
                $order->save();
            }

            return ['status' => $payment->status, 'late' => false];
        }, 3);

        return response()->json($result);
    }
}
