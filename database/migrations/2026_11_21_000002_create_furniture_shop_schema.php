<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('image', 500)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            // stock is available inventory. Checkout must lock this row and decrement atomically.
            $table->unsignedInteger('stock')->default(0);
            $table->string('material', 100)->nullable();
            $table->string('dimensions', 100)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('origin', 100)->nullable();
            $table->string('warranty_period', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['category_id', 'material', 'price'], 'idx_products_filter');
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('path', 500);
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['product_id', 'is_primary'], 'idx_product_images_primary');
        });

        Schema::create('user_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('recipient_name', 150);
            $table->string('phone', 20);
            $table->string('province', 100);
            $table->string('district', 100);
            $table->string('ward', 100);
            $table->string('address_line', 500);
            $table->boolean('is_default')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'is_default'], 'idx_user_addresses_user_default');
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            // Guest carts are keyed by the Laravel session ID and merged into user carts after login/registration.
            $table->string('session_id', 128)->nullable();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('selected')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'product_id'], 'uq_carts_user_product');
            $table->unique(['session_id', 'product_id'], 'uq_carts_session_product');
            $table->index('session_id', 'idx_carts_session');
        });
        DB::statement("ALTER TABLE carts ADD CONSTRAINT chk_carts_owner CHECK ((user_id IS NOT NULL AND session_id IS NULL) OR (user_id IS NULL AND session_id IS NOT NULL))");
        DB::statement('ALTER TABLE carts ADD CONSTRAINT chk_carts_quantity CHECK (quantity > 0)');

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description')->nullable();
            $table->enum('discount_type', ['percent', 'fixed']);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE coupons ADD CONSTRAINT chk_coupons_discount_value CHECK (discount_value >= 0 AND min_order_amount >= 0 AND (max_discount IS NULL OR max_discount >= 0))');

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_code', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name', 150);
            $table->string('phone', 20);
            $table->string('address', 500);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['preparing', 'awaiting_shipment', 'shipping', 'delivered', 'cancelled'])->default('preparing');
            $table->enum('payment_method', ['cod', 'online_demo', 'vnpay', 'vietqr'])->default('cod');
            $table->string('payment_status', 30)->default('pending');
            $table->string('shipping_carrier', 50)->nullable();
            $table->string('tracking_code', 100)->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->enum('cancelled_by', ['customer', 'admin'])->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index('coupon_id', 'idx_orders_coupon_id');
        });
        DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_cancel_reason CHECK (status <> 'cancelled' OR (cancel_reason IS NOT NULL AND cancelled_by IS NOT NULL AND cancelled_at IS NOT NULL))");

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->string('product_image', 500)->nullable();
            $table->string('sku', 50)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
            $table->index('product_id', 'idx_order_items_product_id');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('method', 30);
            $table->string('provider', 30)->default('demo');
            $table->string('status', 30)->default('pending');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('transaction_code', 100)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->timestamps();
            $table->index('order_id', 'idx_payments_order_id');
        });

        Schema::create('return_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('return_code', 30)->unique();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('return_type', ['exchange', 'refund'])->default('refund');
            $table->string('status', 30)->default('requested');
            $table->text('reason');
            $table->string('evidence_image', 500)->nullable();
            $table->text('admin_note')->nullable();
            $table->text('reject_reason')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            // Intentionally non-unique: one order may have multiple return requests.
            $table->index(['order_id', 'status'], 'idx_return_requests_order_status');
            $table->index('user_id', 'idx_return_requests_user');
        });

        Schema::create('return_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('reason', 255)->nullable();
            $table->unique(['return_request_id', 'order_item_id'], 'uq_return_request_items');
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('image', 500)->nullable();
            $table->text('admin_reply')->nullable();
            $table->string('status', 20)->default('published');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['user_id', 'product_id'], 'uq_reviews_user_product');
            $table->index(['product_id', 'status'], 'idx_reviews_product_status');
        });
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)');

        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('from_status', ['preparing', 'awaiting_shipment', 'shipping', 'delivered', 'cancelled'])->nullable();
            $table->enum('to_status', ['preparing', 'awaiting_shipment', 'shipping', 'delivered', 'cancelled']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('actor_type', ['customer', 'admin', 'system']);
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'created_at'], 'idx_order_status_history_timeline');
        });

        Schema::create('product_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('movement_type', ['order_deducted', 'order_cancelled_restocked', 'admin_adjustment']);
            $table->enum('direction', ['in', 'out']);
            $table->unsignedInteger('quantity');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['order_item_id', 'movement_type'], 'uq_stock_movement_order_item_type');
            $table->index(['product_id', 'created_at'], 'idx_stock_movements_product_date');
        });
        DB::statement('ALTER TABLE product_stock_movements ADD CONSTRAINT chk_stock_movement_quantity CHECK (quantity > 0)');

        Schema::create('chat_conversations', function (Blueprint $table): void {
            $table->id();
            // session_id is supplied by the just-chat widget; it is distinct from Laravel's session ID.
            $table->string('session_id', 128)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page_url', 2048)->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'last_message_at'], 'idx_chat_conversations_user_recent');
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->enum('sender', ['user', 'assistant', 'system']);
            $table->longText('content');
            $table->string('status', 20)->default('sent');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['conversation_id', 'created_at'], 'idx_chat_messages_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('product_stock_movements');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('return_request_items');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};