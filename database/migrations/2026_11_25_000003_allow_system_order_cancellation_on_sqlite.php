<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('cancelled_by', ['customer', 'admin', 'system'])->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        if (DB::table('orders')->where('cancelled_by', 'system')->exists()) {
            throw new RuntimeException('Cannot remove the system cancellation actor while system-cancelled orders exist.');
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('cancelled_by', ['customer', 'admin'])->nullable()->change();
        });
    }
};
