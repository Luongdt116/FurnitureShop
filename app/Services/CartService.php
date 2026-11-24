<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function visibleItems(?User $user, string $sessionId, ?string $pendingGuestSessionId = null): Collection
    {
        return $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
            ->with(['product.images'])
            ->orderBy('id')
            ->get();
    }

    public function add(?User $user, string $sessionId, ?string $pendingGuestSessionId, int $productId, int $quantity): Product
    {
        return DB::transaction(function () use ($user, $sessionId, $pendingGuestSessionId, $productId, $quantity): Product {
            $product = Product::query()->where('is_active', true)->orderBy('id')->lockForUpdate()->find($productId);
            if (!$product) {
                throw ValidationException::withMessages(['product_id' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.']);
            }

            $existing = $this->ownerQuery($user, $sessionId)->where('product_id', $productId)->lockForUpdate()->first();
            $visibleQuantity = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
                ->where('product_id', $productId)
                ->sum('quantity');

            if ($visibleQuantity + $quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Sản phẩm {$product->name} hiện chỉ còn {$product->stock} sản phẩm trong kho.",
                ]);
            }

            if ($existing) {
                $existing->increment('quantity', $quantity);
            } else {
                $this->ownerQuery($user, $sessionId)->create([
                    ...$this->ownerAttributes($user, $sessionId),
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'selected' => true,
                ]);
            }

            return $product;
        });
    }

    public function updateQuantity(?User $user, string $sessionId, ?string $pendingGuestSessionId, int $cartId, int $quantity): void
    {
        DB::transaction(function () use ($user, $sessionId, $pendingGuestSessionId, $cartId, $quantity): void {
            $candidate = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)->find($cartId);
            if (!$candidate) {
                abort(404);
            }

            $product = Product::query()->where('is_active', true)->orderBy('id')->lockForUpdate()->find($candidate->product_id);
            if (!$product) {
                throw ValidationException::withMessages(['quantity' => 'Sản phẩm đã ngừng kinh doanh. Hãy xóa sản phẩm khỏi giỏ hàng.']);
            }

            $row = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
                ->lockForUpdate()
                ->find($cartId);
            if (!$row) {
                abort(404);
            }

            $otherQuantity = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
                ->where('product_id', $product->id)
                ->where('id', '!=', $row->id)
                ->sum('quantity');

            if ($otherQuantity + $quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Sản phẩm {$product->name} hiện chỉ còn {$product->stock} sản phẩm trong kho.",
                ]);
            }

            $row->quantity = $quantity;
            $row->save();
        });
    }

    public function updateProductQuantity(?User $user, string $sessionId, ?string $pendingGuestSessionId, int $productId, int $quantity): void
    {
        DB::transaction(function () use ($user, $sessionId, $pendingGuestSessionId, $productId, $quantity): void {
            $product = Product::query()->where('is_active', true)->orderBy('id')->lockForUpdate()->find($productId);
            if (!$product) {
                throw ValidationException::withMessages(['quantity' => 'Sản phẩm đã ngừng kinh doanh. Hãy xóa sản phẩm khỏi giỏ hàng.']);
            }

            if ($quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Sản phẩm {$product->name} hiện chỉ còn {$product->stock} sản phẩm trong kho.",
                ]);
            }

            $rows = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
                ->where('product_id', $productId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                abort(404);
            }

            $primaryRow = $user
                ? ($rows->firstWhere('user_id', $user->id) ?? $rows->first())
                : $rows->first();
            $primaryRow->quantity = $quantity;
            $primaryRow->save();

            $rows->where('id', '!=', $primaryRow->id)->each->delete();
        });
    }

    public function deleteItem(?User $user, string $sessionId, ?string $pendingGuestSessionId, int $cartId): void
    {
        $row = $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)->find($cartId);
        if (!$row) {
            abort(404);
        }

        $row->delete();
    }

    public function deleteProduct(?User $user, string $sessionId, ?string $pendingGuestSessionId, int $productId): void
    {
        $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clear(?User $user, string $sessionId, ?string $pendingGuestSessionId): void
    {
        $this->visibleItemsQuery($user, $sessionId, $pendingGuestSessionId)->delete();
    }

    /**
     * Merge a guest cart as one transaction. A stock conflict rolls back the
     * entire merge, leaving the guest rows available for the signed-in user.
     */
    public function mergeGuestCart(User $user, string $guestSessionId): void
    {
        DB::transaction(function () use ($user, $guestSessionId): void {
            $guestRows = Cart::query()
                ->where('session_id', $guestSessionId)
                ->orderBy('product_id')
                ->get();

            if ($guestRows->isEmpty()) {
                return;
            }

            $productIds = $guestRows->pluck('product_id')->unique()->sort()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $guestRows = Cart::query()
                ->where('session_id', $guestSessionId)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            $userRows = Cart::query()
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($guestRows as $guestRow) {
                $product = $products->get($guestRow->product_id);
                $userRow = $userRows->get($guestRow->product_id);
                $targetQuantity = $guestRow->quantity + ($userRow?->quantity ?? 0);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một sản phẩm trong giỏ khách đã ngừng kinh doanh. Giỏ chưa được gộp; vui lòng xóa sản phẩm đó.',
                    ]);
                }

                if ($targetQuantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Không thể gộp giỏ: {$product->name} cần {$targetQuantity} sản phẩm nhưng kho chỉ còn {$product->stock}. Giỏ khách vẫn được giữ lại; hãy điều chỉnh số lượng rồi thử lại.",
                    ]);
                }
            }

            foreach ($guestRows as $guestRow) {
                $userRow = $userRows->get($guestRow->product_id);
                if ($userRow) {
                    $userRow->quantity += $guestRow->quantity;
                    $userRow->selected = $userRow->selected || $guestRow->selected;
                    $userRow->save();
                } else {
                    Cart::query()->create([
                        'user_id' => $user->id,
                        'product_id' => $guestRow->product_id,
                        'quantity' => $guestRow->quantity,
                        'selected' => $guestRow->selected,
                    ]);
                }
            }

            Cart::query()->where('session_id', $guestSessionId)->delete();
        });
    }

    /** Refresh the legacy drawer/header snapshot from the carts table. */
    public function legacySnapshot(?User $user, string $sessionId, ?string $pendingGuestSessionId = null): array
    {
        return $this->visibleItems($user, $sessionId, $pendingGuestSessionId)
            ->groupBy('product_id')
            ->map(function (Collection $rows): array {
                /** @var Cart $row */
                $row = $rows->first();
                $product = $row->product;
                $quantity = (int) $rows->sum('quantity');
                $imagePath = $product?->images->firstWhere('is_primary', true)?->path
                    ?? $product?->images->first()?->path
                    ?? $product?->image;

                if ($imagePath && (str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, 'http://'))) {
                    $image = $imagePath;
                } else {
                    $image = $imagePath ? asset($imagePath) : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80';
                }

                return [
                    'id' => $row->product_id,
                    'name' => $product?->name ?? 'Sản phẩm không còn khả dụng',
                    'slug' => $product?->slug ?? '#',
                    'price' => (float) ($product?->final_price ?? $product?->price ?? 0),
                    'original_price' => (float) ($product?->price ?? 0),
                    'image' => $image,
                    'color' => $product?->color ?? '',
                    'stock' => (int) ($product?->stock ?? 0),
                    'quantity' => $quantity,
                ];
            })
            ->all();
    }

    private function ownerQuery(?User $user, string $sessionId): Builder
    {
        return Cart::query()->when(
            $user,
            fn (Builder $query) => $query->where('user_id', $user->id),
            fn (Builder $query) => $query->where('session_id', $sessionId)
        );
    }

    private function ownerAttributes(?User $user, string $sessionId): array
    {
        return $user
            ? ['user_id' => $user->id, 'session_id' => null]
            : ['user_id' => null, 'session_id' => $sessionId];
    }

    private function visibleItemsQuery(?User $user, string $sessionId, ?string $pendingGuestSessionId): Builder
    {
        if (!$user || !$pendingGuestSessionId) {
            return $this->ownerQuery($user, $sessionId);
        }

        return Cart::query()->where(function (Builder $query) use ($user, $pendingGuestSessionId): void {
            $query->where('user_id', $user->id)->orWhere('session_id', $pendingGuestSessionId);
        });
    }
}
