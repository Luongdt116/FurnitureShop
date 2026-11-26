<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'provider',
        'status',
        'amount',
        'transaction_code',
        'paid_at',
        'payment_expires_at',
        'refunded_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $onlineMethods = ['online_demo', 'vnpay', 'vietqr'];
            $status = $payment->status ?? 'pending';

            if (in_array($payment->method, $onlineMethods, true)
                && $status === 'pending'
                && ! $payment->payment_expires_at) {
                $payment->payment_expires_at = $payment->order_id
                    ? Order::query()->whereKey($payment->order_id)->value('payment_expires_at')
                    : null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'payment_expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
