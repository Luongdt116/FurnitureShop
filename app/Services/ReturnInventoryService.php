<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnInventoryService
{
    /**
     * Restock returned goods after inspection, as the return is completed.
     * The per-return-line movement key makes retries safe.
     */
    public function restockInspectedReturn(ReturnRequest $returnRequest, User $actor): void
    {
        if (! $actor->isAdmin() || ! $actor->is_active) {
            throw ValidationException::withMessages([
                'actor' => 'Tài khoản không có quyền ghi nhận hàng đổi/trả vào kho.',
            ]);
        }

        DB::transaction(function () use ($returnRequest, $actor): void {
            $lockedRequest = ReturnRequest::query()
                ->whereKey($returnRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== 'inspected') {
                throw ValidationException::withMessages([
                    'status' => 'Chỉ được nhập lại kho sau khi yêu cầu đã kiểm tra xong.',
                ]);
            }

            $items = $lockedRequest->items()->orderBy('id')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Yêu cầu đổi/trả không có sản phẩm để nhập lại kho.',
                ]);
            }

            foreach ($items as $item) {
                $quantity = (int) $item->quantity;
                if ($quantity < 1) {
                    throw ValidationException::withMessages([
                        'items' => 'Số lượng đổi/trả không hợp lệ.',
                    ]);
                }

                $existingMovement = ProductStockMovement::query()
                    ->where('return_request_item_id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingMovement) {
                    if ($existingMovement->movement_type !== 'return_restocked'
                        || $existingMovement->direction !== 'in'
                        || (int) $existingMovement->quantity !== $quantity) {
                        throw ValidationException::withMessages([
                            'items' => 'Biến động kho hiện có không khớp với dòng đổi/trả.',
                        ]);
                    }

                    continue;
                }

                $orderItem = $item->orderItem()->firstOrFail();
                $product = Product::withTrashed()
                    ->whereKey($orderItem->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product->stock = (int) $product->stock + $quantity;
                $product->save();

                ProductStockMovement::create([
                    'product_id' => $product->id,
                    // Return movements use their request-line key; the existing order-item
                    // unique key remains reserved for order deduction/cancellation movements.
                    'order_item_id' => null,
                    'return_request_item_id' => $item->id,
                    'movement_type' => 'return_restocked',
                    'direction' => 'in',
                    'quantity' => $quantity,
                    'actor_user_id' => $actor->id,
                    'note' => 'Nhập lại kho sau khi kiểm tra yêu cầu ' . $lockedRequest->return_code,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
