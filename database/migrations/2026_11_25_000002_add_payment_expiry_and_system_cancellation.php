<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dateTime('payment_expires_at')->nullable();
            $table->index(['payment_method', 'payment_status', 'payment_expires_at'], 'idx_orders_payment_expiry');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY cancelled_by ENUM('customer', 'admin', 'system') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            if (DB::table('orders')->where('cancelled_by', 'system')->exists()) {
                throw new RuntimeException('Cannot remove the system cancellation actor while system-cancelled orders exist.');
            }

            DB::statement("ALTER TABLE orders MODIFY cancelled_by ENUM('customer', 'admin') NULL");
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('idx_orders_payment_expiry');
            $table->dropColumn('payment_expires_at');
        });
    }
};
