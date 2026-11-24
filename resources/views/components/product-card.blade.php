@props([
    'product',
])

@php
    $id = is_array($product) ? ($product['id'] ?? '') : $product->id;
    $name = is_array($product) ? ($product['name'] ?? '') : $product->name;
    $slug = is_array($product) ? ($product['slug'] ?? $id) : ($product->slug ?? $product->id);
    $price = is_array($product) ? ($product['price'] ?? 0) : $product->price;
    $discountPercent = is_array($product) ? ($product['discount_percent'] ?? 0) : ($product->discount_percent ?? 0);
    $originalPrice = is_array($product) ? ($product['original_price'] ?? null) : ($product->original_price ?? null);
    if (!$originalPrice && $discountPercent > 0) {
        $originalPrice = round($price / (1 - ($discountPercent / 100)));
    }
    
    // Category & Room
    if (is_array($product)) {
        $catName = $product['category_name'] ?? $product['category']['name'] ?? 'Nội thất cao cấp';
        $roomType = $product['room_type'] ?? 'Không gian sống';
    } elseif ($product instanceof \Illuminate\Database\Eloquent\Model) {
        $catName = $product->category->name ?? 'Nội thất cao cấp';
        $roomType = $product->room_type ?? 'Không gian sống';
    } else {
        $catName = $product->category_name ?? ($product->category->name ?? 'Nội thất cao cấp');
        $roomType = $product->room_type ?? 'Không gian sống';
    }
    
    // Image
    $image = 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80';
    if (is_array($product)) {
        if (!empty($product['images'][0])) {
            $image = $product['images'][0];
        } elseif (!empty($product['image'])) {
            $image = $product['image'];
        }
    } elseif ($product instanceof \Illuminate\Database\Eloquent\Model) {
        if ($product->relationLoaded('images') && $product->images->isNotEmpty()) {
            $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $image = asset($primaryImg->path);
        } elseif (!empty($product->image)) {
            $image = asset($product->image);
        }
    } else {
        if (!empty($product->images) && is_array($product->images) && !empty($product->images[0])) {
            $image = $product->images[0];
        } elseif (!empty($product->image)) {
            $image = asset($product->image);
        }
    }

    // Specs
    $dimensions = is_array($product) ? ($product['dimensions'] ?? '') : ($product->dimensions ?? '');
    $material = is_array($product) ? ($product['material'] ?? $product['wood_material'] ?? '') : ($product->material ?? '');
    $rating = is_array($product) ? ($product['rating'] ?? 5.0) : ($product->rating ?? 5.0);
    $reviewCount = is_array($product) ? ($product['review_count'] ?? 12) : ($product->reviews_count ?? 12);
    $isBestSeller = is_array($product) ? ($product['is_bestseller'] ?? false) : ($product->is_bestseller ?? false);

    $detailUrl = route('product.detail', $slug);
@endphp

<div class="product-card" id="product-card-{{ $id }}" data-prod-id="{{ $id }}">
    <div class="product-thumb-wrap" onclick="window.location.href='{{ $detailUrl }}'">
        <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">
        <div class="product-badges">
            @if ($discountPercent > 0)
                <span class="badge badge-gold">-{{ $discountPercent }}%</span>
            @endif
            @if ($isBestSeller)
                <span class="badge badge-primary">Bán chạy</span>
            @endif
        </div>
        <button type="button" class="quick-view-btn" onclick="event.stopPropagation(); window.location.href='{{ $detailUrl }}'">
            <i class="fas fa-eye"></i> Xem chi tiết
        </button>
    </div>

    <div class="product-body">
        <div class="product-cat">{{ $roomType }} &bull; {{ $catName }}</div>
        <h4 class="product-title" onclick="window.location.href='{{ $detailUrl }}'">
            <a href="{{ $detailUrl }}" class="text-decoration-none text-reset">{{ $name }}</a>
        </h4>

        @if ($dimensions || $material)
            <div class="product-specs-preview">
                {{ $dimensions }} {{ $dimensions && $material ? '•' : '' }} {{ Str::limit($material, 25) }}
            </div>
        @endif

        <div class="product-rating">
            <span><i class="fas fa-star text-warning"></i> {{ number_format($rating, 1) }}</span>
            <span class="count">({{ $reviewCount }} đánh giá)</span>
        </div>

        <div class="product-footer mt-auto pt-2">
            <div class="product-prices">
                <span class="product-price">{{ number_format($price, 0, ',', '.') }}₫</span>
                @if ($originalPrice && $originalPrice > $price)
                    <span class="product-old-price">{{ number_format($originalPrice, 0, ',', '.') }}₫</span>
                @endif
            </div>

            <form action="{{ route('cart.add') }}" method="POST" class="d-inline m-0 p-0" onsubmit="event.stopPropagation();">
                @csrf
                <input type="hidden" name="product_id" value="{{ $id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn-add-cart-icon border-0" title="Thêm vào giỏ hàng" id="product-btn-add-{{ $id }}">
                    <i class="fas fa-shopping-bag"></i>
                </button>
            </form>
        </div>
    </div>
</div>
