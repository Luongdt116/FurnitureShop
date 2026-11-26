<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Đánh giá từ 1 đến 5 sao.',
            'image.image' => 'Ảnh đánh giá phải là tệp hình ảnh hợp lệ.',
        ]);

        $user = $request->user();
        $ownedOrder = $user->orders()->whereKey($order->getKey())->firstOrFail();

        if ($ownedOrder->status !== 'delivered' || (int) $orderItem->order_id !== (int) $ownedOrder->id) {
            abort(404);
        }

        $orderItem = $ownedOrder->items()->whereKey($orderItem->getKey())->firstOrFail();
        $alreadyReviewed = Review::withTrashed()
            ->where('user_id', $user->id)
            ->where('product_id', $orderItem->product_id)
            ->exists();

        if ($alreadyReviewed || Review::withTrashed()->where('order_item_id', $orderItem->id)->exists()) {
            throw ValidationException::withMessages([
                'rating' => 'Sản phẩm trong đơn hàng này đã được đánh giá.',
            ]);
        }

        $storedImage = $request->hasFile('image')
            ? $request->file('image')->store('reviews', 'public')
            : null;

        try {
            DB::transaction(function () use ($user, $orderItem, $validated, $storedImage) {
                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $orderItem->product_id,
                    'order_item_id' => $orderItem->id,
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                    'image' => $storedImage,
                    'status' => 'published',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($storedImage) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }

        return redirect()
            ->route('orders.show', $ownedOrder)
            ->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm.');
    }
}
