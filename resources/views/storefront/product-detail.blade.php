@extends('layouts.storefront')

@php
    $pId = is_array($product) ? ($product['id'] ?? 1) : $product->id;
    $pName = is_array($product) ? ($product['name'] ?? '') : $product->name;
    $pSku = is_array($product) ? ($product['sku'] ?? '') : $product->sku;
    $pPrice = is_array($product) ? ($product['price'] ?? 0) : $product->price;
    $pDiscount = is_array($product) ? ($product['discount_percent'] ?? 0) : ($product->discount_percent ?? 0);
    $pOrigPrice = is_array($product) ? ($product['original_price'] ?? null) : ($product->original_price ?? null);
    if (!$pOrigPrice && $pDiscount > 0) {
        $pOrigPrice = round($pPrice / (1 - ($pDiscount / 100)));
    }
    $pStock = is_array($product) ? ($product['stock'] ?? 10) : ($product->stock ?? 10);
    $pDim = is_array($product) ? ($product['dimensions'] ?? '') : ($product->dimensions ?? '');
    $pMat = is_array($product) ? ($product['material'] ?? $product['wood_material'] ?? '') : ($product->material ?? '');
    $pColor = is_array($product) ? ($product['color'] ?? 'Nâu Trầm, Tự Nhiên') : ($product->color ?? 'Nâu Trầm, Tự Nhiên');
    $pOrigin = is_array($product) ? ($product['origin'] ?? 'Việt Nam xuất khẩu') : ($product->origin ?? 'Việt Nam xuất khẩu');
    $pWarranty = is_array($product) ? ($product['warranty_period'] ?? '5 năm khung kết cấu') : ($product->warranty_period ?? '5 năm khung kết cấu');
    $pDesc = is_array($product) ? ($product['description'] ?? '') : ($product->description ?? '');
    $pRating = is_array($product) ? ($product['rating'] ?? 5.0) : ($product->rating ?? 5.0);
    $pReviewCount = is_array($product) ? ($product['review_count'] ?? 12) : ($product->reviews_count ?? 12);
    // Category & Room
    if (is_array($product)) {
        $catName = $product['category_name'] ?? $product['category']['name'] ?? 'Nội thất';
    } elseif ($product instanceof \Illuminate\Database\Eloquent\Model) {
        $catName = $product->category->name ?? 'Nội thất';
    } else {
        $catName = $product->category_name ?? ($product->category->name ?? 'Nội thất');
    }

    // Images
    $imgList = [];
    if (is_array($product)) {
        if (!empty($product['images'])) {
            $imgList = $product['images'];
        } elseif (!empty($product['image'])) {
            $imgList = [$product['image']];
        }
    } elseif ($product instanceof \Illuminate\Database\Eloquent\Model) {
        if ($product->relationLoaded('images') && $product->images->isNotEmpty()) {
            $imgList = $product->images->pluck('path')->map(fn($p) => asset($p))->toArray();
        } elseif (!empty($product->image)) {
            $imgList = [asset($product->image)];
        }
    } else {
        if (!empty($product->images) && is_array($product->images)) {
            $imgList = $product->images;
        } elseif (!empty($product->image)) {
            $imgList = [$product->image];
        }
    }

    if (empty($imgList)) {
        $imgList = [
            'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
        ];
    }
    $mainImg = $imgList[0];
@endphp

@section('title', "{$pName} - AURA LIVING")
@section('meta_description', Str::limit(strip_tags($pDesc), 150))

@section('content')
<div class="container py-4 py-lg-5" id="product-detail-page">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size: 0.88rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang Chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}" class="text-decoration-none text-muted">Sản Phẩm</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog.index', ['category' => Str::slug($catName)]) }}" class="text-decoration-none text-muted">{{ $catName }}</a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="color: var(--accent); font-weight: 600; max-width: 320px;">{{ $pName }}</li>
        </ol>
    </nav>

    {{-- Main Product Detail Card --}}
    <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-5">
        <div class="product-detail-grid">
            {{-- 1. Image Gallery --}}
            <div class="detail-gallery">
                <div class="detail-main-img">
                    <img id="product-detail-main-img" src="{{ $mainImg }}" alt="{{ $pName }}" loading="eager">
                </div>
                <div class="detail-thumbs">
                    @foreach($imgList as $idx => $img)
                        <div class="detail-thumb {{ $idx === 0 ? 'active' : '' }}" onclick="switchProductThumb('{{ $img }}', this)">
                            <img src="{{ $img }}" alt="{{ $pName }} thumb {{ $idx + 1 }}">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. Product Information --}}
            <div class="detail-info">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="detail-sku">SKU: <strong>{{ $pSku }}</strong></span>
                    <span class="badge badge-primary">{{ $catName }}</span>
                </div>

                <h1 class="detail-title">{{ $pName }}</h1>

                <div class="product-rating mb-3">
                    <span><i class="fas fa-star text-warning"></i> {{ number_format($pRating, 1) }}</span>
                    <span class="count">({{ $pReviewCount }} lượt đánh giá thực tế)</span>
                    <span class="ms-3 text-success small font-weight-bold"><i class="fas fa-check-circle"></i> Còn {{ $pStock }} sản phẩm</span>
                </div>

                <div class="detail-price-box">
                    <span class="detail-price">{{ number_format($pPrice, 0, ',', '.') }}₫</span>
                    @if($pOrigPrice && $pOrigPrice > $pPrice)
                        <span class="detail-old-price">{{ number_format($pOrigPrice, 0, ',', '.') }}₫</span>
                    @endif
                    @if($pDiscount > 0)
                        <span class="badge badge-gold">Tiết kiệm {{ $pDiscount }}%</span>
                    @endif
                </div>

                {{-- Color options --}}
                <div class="detail-options-group">
                    <div class="detail-options-title">Tùy chọn màu sắc & phiên bản:</div>
                    <div class="color-pills">
                        @foreach(explode(',', $pColor) as $cIdx => $colorOption)
                            <div class="color-pill {{ $cIdx === 0 ? 'active' : '' }}" onclick="selectProductColor('{{ trim($colorOption) }}', this)">
                                {{ trim($colorOption) }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Specifications Table --}}
                <table class="detail-specs-table">
                    <tr><td>Kích thước tiêu chuẩn</td><td><strong>{{ $pDim }}</strong></td></tr>
                    <tr><td>Chất liệu khung & gỗ</td><td>{{ $pMat }}</td></tr>
                    <tr><td>Chính sách bảo hành</td><td><strong>{{ $pWarranty }}</strong></td></tr>
                    <tr><td>Xuất xứ & gia công</td><td>{{ $pOrigin }}</td></tr>
                    <tr><td>Cam kết chất lượng</td><td>100% Gỗ tự nhiên & da bò đạt chuẩn E1 châu Âu</td></tr>
                </table>

                <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 24px;">
                    {{ $pDesc }}
                </p>

                {{-- Add to Cart Form --}}
                <form action="{{ route('cart.add') }}" method="POST" id="product-detail-add-form" class="mt-auto">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $pId }}">
                    <input type="hidden" name="color" id="product-selected-color" value="{{ trim(explode(',', $pColor)[0]) }}">
                    
                    <div class="detail-actions-row">
                        <div class="qty-counter">
                            <button type="button" class="qty-btn" onclick="adjustProductQty(-1)" aria-label="Giảm số lượng"><i class="fas fa-minus"></i></button>
                            <input id="product-detail-qty-input" name="quantity" class="qty-input" type="text" value="1" readonly>
                            <button type="button" class="qty-btn" onclick="adjustProductQty(1)" aria-label="Tăng số lượng"><i class="fas fa-plus"></i></button>
                        </div>
                        <button type="submit" class="btn btn-outline flex-fill" id="product-btn-add-cart">
                            <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ
                        </button>
                        <button type="submit" name="buy_now" value="1" class="btn btn-accent flex-fill" id="product-btn-buy-now">
                            Mua ngay &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =======================================================================
         CUSTOMER REVIEWS SECTION
         ======================================================================= --}}
    <section class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-5" id="product-reviews-section">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h3 class="h4 mb-1 font-heading">Đánh Giá Thực Tế Từ Khách Hàng ({{ count($reviews) }})</h3>
                <span class="text-muted small">Ý kiến trải nghiệm chân thực từ các gia chủ đã tin dùng</span>
            </div>
            <span class="badge badge-success px-3 py-2 mt-2 mt-sm-0"><i class="fas fa-shield-alt me-1"></i> 100% Đánh giá từ người mua thật</span>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($reviews as $rev)
                <div class="p-3 p-md-4 rounded-3" style="background: var(--bg-subtle);">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ $rev->user_avatar ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80' }}" 
                             alt="{{ $rev->user_name ?? 'Khách hàng' }}" 
                             style="width: 42px; height: 42px; border-radius: var(--radius-full); object-fit: cover;">
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">
                                {{ $rev->user_name ?? 'Khách Hàng AURA' }} 
                                <span class="badge badge-gold ms-1" style="font-size: 0.65rem;">Người mua xác thực</span>
                            </div>
                            <div class="text-warning small">
                                @for($i = 0; $i < ($rev->rating ?? 5); $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                                <span class="text-muted ms-2" style="font-size: 0.75rem;">{{ $rev->created_at ?? date('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @if(!empty($rev->title))
                        <div class="fw-bold mb-1" style="font-size: 0.95rem; color: var(--primary);">{{ $rev->title }}</div>
                    @endif
                    <p class="mb-0 text-secondary" style="font-size: 0.88rem; line-height: 1.6;">
                        {{ $rev->comment ?? '' }}
                    </p>
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên trải nghiệm!</p>
            @endforelse
        </div>
    </section>

    {{-- =======================================================================
         RELATED PRODUCTS SECTION
         ======================================================================= --}}
    @if(!empty($relatedProducts) && count($relatedProducts) > 0)
        <section class="mt-5" id="product-related-section">
            <div class="section-header mb-4">
                <div>
                    <span class="section-tag">Gợi Ý Hoàn Hảo</span>
                    <h2 class="section-title h3">Tuyệt Tác Tương Tự Cùng Phong Cách</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm">Xem tất cả &rarr;</a>
            </div>
            <div class="products-grid">
                @foreach($relatedProducts as $relProd)
                    <x-product-card :product="$relProd" />
                @endforeach
            </div>
        </section>
    @endif
</div>

@push('scripts')
<script>
    function switchProductThumb(imgSrc, el) {
        const mainImg = document.getElementById('product-detail-main-img');
        if (mainImg) mainImg.src = imgSrc;
        document.querySelectorAll('.detail-thumb').forEach(t => t.classList.remove('active'));
        if (el) el.classList.add('active');
    }

    function selectProductColor(color, el) {
        const hiddenInput = document.getElementById('product-selected-color');
        if (hiddenInput) hiddenInput.value = color;
        document.querySelectorAll('.color-pill').forEach(p => p.classList.remove('active'));
        if (el) el.classList.add('active');
    }

    function adjustProductQty(delta) {
        const input = document.getElementById('product-detail-qty-input');
        if (!input) return;
        let val = Number(input.value) + delta;
        if (val < 1) val = 1;
        const maxStock = {{ $pStock }};
        if (val > maxStock) {
            alert('Kho chỉ còn ' + maxStock + ' sản phẩm.');
            val = maxStock;
        }
        input.value = val;
    }
</script>
@endpush
@endsection
