<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function place(
        User $user,
        array $shipping,
        string $idempotencyKey,
        string $sessionId,
        ?string $pendingGuestSessionId = null
    ): Order {
        try {
            return DB::transaction(function () use ($user, $shipping, $idempotencyKey, $sessionId, $pendingGuestSessionId): Order {
                $existingOrder = Order::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existingOrder) {
                    if ($existingOrder->user_id !== $user->id) {
                        throw ValidationException::withMessages(['checkout' => 'Mã xác nhận đơn hàng không hợp lệ. Vui lòng tải lại trang thanh toán.']);
                    }

                    return $existingOrder;
                }

                $cartQuery = $this->cartQuery($user, $sessionId, $pendingGuestSessionId);
                $initialRows = (clone $cartQuery)->get(['id', 'product_id', 'quantity']);
                if ($initialRows->isEmpty()) {
                    throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
                }

                $productIds = $initialRows->pluck('product_id')->unique()->sort()->values();
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $products->load('images');

                // Product rows are locked in ascending ID order before cart rows,
                // matching the cart mutation lock order and reducing deadlocks.
                $cartRows = (clone $cartQuery)
                    ->orderBy('product_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $lockedProductIds = $cartRows->pluck('product_id')->unique()->sort()->values();
                if ($lockedProductIds->all() !== $productIds->all()) {
                    throw ValidationException::withMessages(['cart' => 'Giỏ hàng vừa thay đổi. Vui lòng kiểm tra lại rồi đặt hàng.']);
                }

                $quantities = $cartRows->groupBy('product_id')
                    ->map(fn (Collection $rows): int => (int) $rows->sum('quantity'));

                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    $quantity = $quantities->get($productId, 0);

                    if (!$product || !$product->is_active) {
                        throw ValidationException::withMessages(['cart' => 'Một sản phẩm trong giỏ đã ngừng kinh doanh. Vui lòng xóa sản phẩm đó.']);
                    }

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'cart' => "{$product->name} không đủ hàng: cần {$quantity}, hiện còn {$product->stock}.",
                        ]);
                    }
                }

                $subtotal = 0.0;
                $lineData = [];
                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    $quantity = $quantities->get($productId);
                    $unitPrice = (float) $product->final_price;
                    $lineTotal = round($unitPrice * $quantity, 2);
                    $subtotal += $lineTotal;
                    $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

                    $lineData[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'image' => $image?->path,
                    ];
                }

                $address = implode(', ', array_filter([
                    trim($shipping['address_line']),
                    trim($shipping['ward']),
                    trim($shipping['district']),
                    trim($shipping['province']),
                ]));
                if (mb_strlen($address) > 500) {
                    throw ValidationException::withMessages(['address_line' => 'Địa chỉ giao hàng quá dài. Vui lòng rút gọn địa chỉ.']);
                }
                $shippingFee = 0.0;
                $discountAmount = 0.0;
                $isOnlinePayment = $shipping['payment_method'] !== 'cod';

                $order = Order::query()->create([
                    'order_code' => 'AL-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(5)),
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $user->id,
                    'recipient_name' => $shipping['recipient_name'],
                    'phone' => $shipping['phone'],
                    'address' => $address,
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $discountAmount,
                    'total' => $subtotal + $shippingFee - $discountAmount,
                    'status' => 'preparing',
                    'payment_method' => $shipping['payment_method'],
                    'payment_status' => 'pending',
                    'payment_expires_at' => $isOnlinePayment ? now()->addMinutes(30) : null,
                    'customer_note' => $shipping['note'] ?? null,
                ]);

                foreach ($lineData as $line) {
                    /** @var Product $product */
                    $product = $line['product'];
                    $orderItem = OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_image' => $line['image'],
                        'sku' => $product->sku,
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                    ]);

                    $product->decrement('stock', $line['quantity']);

                    ProductStockMovement::query()->create([
                        'product_id' => $product->id,
                        'order_item_id' => $orderItem->id,
                        'movement_type' => 'order_deducted',
                        'direction' => 'out',
                        'quantity' => $line['quantity'],
                        'actor_user_id' => $user->id,
                        'note' => "Trừ tồn kho cho đơn {$order->order_code}",
                    ]);
                }

                Payment::query()->create([
                    'order_id' => $order->id,
                    'method' => $shipping['payment_method'],
                    'provider' => $shipping['payment_method'] === 'cod' ? 'cash_on_delivery' : 'demo',
                    'status' => 'pending',
                    'amount' => $order->total,
                ]);

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => 'preparing',
                    'changed_by' => $user->id,
                    'actor_type' => $user->isAdmin() ? 'admin' : 'customer',
                    'reason' => 'Khách hàng đặt hàng',
                ]);

                $cartRows->each->delete();

                return $order->load('items', 'payments');
            }, 3);
        } catch (QueryException $exception) {
            // A unique-key collision from a simultaneous repeat request is an
            // idempotent replay only when the original order now exists.
            $existingOrder = Order::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder && $existingOrder->user_id === $user->id) {
                return $existingOrder;
            }

            throw $exception;
        }
    }

    private function cartQuery(User $user, string $sessionId, ?string $pendingGuestSessionId): Builder
    {
        return Cart::query()->where(function (Builder $query) use ($user, $sessionId, $pendingGuestSessionId): void {
            $query->where('user_id', $user->id);
            $query->orWhere('session_id', $sessionId);
            if ($pendingGuestSessionId) {
                $query->orWhere('session_id', $pendingGuestSessionId);
            }
        });
    }
}
