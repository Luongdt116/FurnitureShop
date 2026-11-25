<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table): void {
            $table->foreignId('return_request_item_id')
                ->nullable()
                ->after('order_item_id')
                ->constrained('return_request_items')
                ->restrictOnDelete();
            $table->unique('return_request_item_id', 'uq_stock_movement_return_item');
        });

        DB::statement("ALTER TABLE product_stock_movements MODIFY movement_type ENUM('order_deducted', 'order_cancelled_restocked', 'admin_adjustment', 'return_restocked') NOT NULL");
    }

    public function down(): void
    {
        if (DB::table('product_stock_movements')->where('movement_type', 'return_restocked')->exists()) {
            throw new RuntimeException('Cannot roll back while return restock movements exist.');
        }

        DB::statement("ALTER TABLE product_stock_movements MODIFY movement_type ENUM('order_deducted', 'order_cancelled_restocked', 'admin_adjustment') NOT NULL");

        Schema::table('product_stock_movements', function (Blueprint $table): void {
            $table->dropUnique('uq_stock_movement_return_item');
            $table->dropForeign(['return_request_item_id']);
            $table->dropColumn('return_request_item_id');
        });
    }
};
