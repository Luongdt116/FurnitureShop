<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dateTime('payment_expires_at')->nullable()->after('paid_at');
        });

        DB::table('payments')
            ->whereIn('method', ['online_demo', 'vnpay', 'vietqr'])
            ->where('status', 'pending')
            ->update([
                'payment_expires_at' => DB::raw('DATE_ADD(created_at, INTERVAL 30 MINUTE)'),
            ]);

        DB::statement("ALTER TABLE orders MODIFY cancelled_by ENUM('customer', 'admin', 'system') NULL");
    }

    public function down(): void
    {
        if (DB::table('orders')->where('cancelled_by', 'system')->exists()) {
            throw new RuntimeException('Cannot roll back while system-cancelled orders exist.');
        }

        DB::statement("ALTER TABLE orders MODIFY cancelled_by ENUM('customer', 'admin') NULL");

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('payment_expires_at');
        });
    }
};
