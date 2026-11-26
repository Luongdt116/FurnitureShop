<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\CancellationService;
use Illuminate\Console\Command;

class ExpireOnlinePayments extends Command
{
    protected $signature = 'payments:expire-online';

    protected $description = 'Cancel unpaid online orders whose payment deadline has passed';

    public function handle(CancellationService $cancellations): int
    {
        Order::query()
            ->whereIn('payment_method', ['online_demo', 'vnpay', 'vietqr'])
            ->whereIn('payment_status', ['pending', 'failed'])
            ->whereIn('status', ['preparing', 'awaiting_shipment'])
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($orders) use ($cancellations): void {
                foreach ($orders as $order) {
                    $cancellations->expireOnlinePayment((int) $order->id);
                }
            });

        return self::SUCCESS;
    }
}
