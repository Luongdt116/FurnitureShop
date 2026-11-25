@extends('layouts.storefront')

@section('title', !empty($searchQuery) ? "Tìm kiếm: {$searchQuery} - AURA LIVING" : 'Danh Mục Sản Phẩm Nội Thất - AURA LIVING')

@section('content')
<div class="container py-4 py-lg-5" id="catalog-page-container">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size: 0.88rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page" style="color: var(--accent); font-weight: 600;">
                @if(!empty($searchQuery))
                    Kết quả tìm kiếm: "{{ $searchQuery }}"
                @elseif($selectedCategory !== 'all')
                    {{ $categories->firstWhere('slug', $selectedCategory)->name ?? 'Sản Phẩm' }}
                @elseif($selectedRoom !== 'all')
                    {{ $selectedRoom }}
                @else
                    Tất Cả Sản Phẩm
                @endif
            </li>
        </ol>
    </nav>

    <div class="catalog-layout" id="catalog-layout">
        {{-- ===================================================================
             FILTER SIDEBAR
             =================================================================== --}}
        <aside class="filter-sidebar" id="catalog-filter-sidebar">
            <div class="filter-title-main">
                <span><i class="fas fa-sliders me-2" style="color: var(--accent);"></i> Bộ Lọc Tìm Kiếm</span>
                <a href="{{ route('catalog.index') }}" class="filter-clear-btn text-decoration-none" id="catalog-clear-filters-btn">Xóa lọc</a>
            </div>

            <form id="catalog-filter-form" action="{{ route('catalog.index') }}" method="GET">
                @if(!empty($searchQuery))
                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                @endif
                <input type="hidden" name="sort" id="catalog-form-sort-input" value="{{ $sort }}">

                {{-- 1. Room / Space Filter --}}
                <div class="filter-section">
                    <div class="filter-heading">Không Gian Phòng</div>
                    <div class="filter-options-list">
                        @php
                            $rooms = [
                                'all' => 'Tất cả không gian',
                                'Phòng Khách' => 'Phòng Khách',
                                'Phòng Ngủ' => 'Phòng Ngủ',
                                'Phòng Ăn' => 'Phòng Ăn & Bếp',
                                'Phòng Làm Việc' => 'Phòng Làm Việc',
                            ];
                        @endphp
                        @foreach($rooms as $rKey => $rLabel)
                            <button type="submit" name="room" value="{{ $rKey }}" 
                                    class="filter-room-btn btn btn-ghost btn-sm w-100 text-start {{ $selectedRoom === $rKey ? 'active' : '' }}"
                                    style="{{ $selectedRoom === $rKey ? 'background-color: var(--bg-subtle); color: var(--accent); font-weight: 700;' : '' }}">
                                {{ $rLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Category Filter --}}
                <div class="filter-section">
                    <div class="filter-heading">Danh Mục Nội Thất</div>
                    <div class="filter-options-list">
                        <label class="filter-checkbox-label">
                            <input type="radio" name="category" value="all" {{ $selectedCategory === 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                            Tất cả danh mục
                        </label>
                        @foreach($categories as $cat)
                            <label class="filter-checkbox-label">
                                <input type="radio" name="category" value="{{ $cat->slug }}" {{ $selectedCategory === $cat->slug ? 'checked' : '' }} onchange="this.form.submit()">
                                {{ $cat->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Price Range Slider --}}
                <div class="filter-section">
                    <div class="filter-heading">Mức Giá Tối Đa</div>
                    <div class="price-slider-wrap">
                        <input id="catalog-price-slider" name="max_price" class="price-range-input" type="range" 
                               min="3000000" max="35000000" step="1000000" 
                               value="{{ $maxPrice }}" 
                               oninput="document.getElementById('catalog-price-display').innerText = Number(this.value).toLocaleString('vi-VN') + '₫'"
                               onchange="this.form.submit()">
                        <div class="price-val-display">
                            <span>3.000.000₫</span>
                            <span id="catalog-price-display">{{ number_format($maxPrice, 0, ',', '.') }}₫</span>
                        </div>
                    </div>
                </div>

                {{-- 4. Material Filter --}}
                <div class="filter-section">
                    <div class="filter-heading">Chất Liệu Gỗ & Bề Mặt</div>
                    <div class="filter-options-list">
                        @php
                            $materials = [
                                'sồi' => 'Gỗ sồi trắng Bắc Mỹ (White Oak)',
                                'óc chó' => 'Gỗ óc chó FAS (Walnut)',
                                'da' => 'Da bò thật nhập khẩu Italy',
                                'đá' => 'Mặt đá Ceramic chống trầy',
                            ];
                        @endphp
                        @foreach($materials as $mKey => $mLabel)
                            <label class="filter-checkbox-label">
                                <input type="radio" name="material" value="{{ $mKey }}" {{ $selectedMaterial === $mKey ? 'checked' : '' }} onchange="this.form.submit()">
                                {{ $mLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        </aside>

        {{-- ===================================================================
             PRODUCTS GRID COLUMN
             =================================================================== --}}
        <div>
            {{-- Toolbar --}}
            <div class="catalog-toolbar" id="catalog-toolbar">
                <div id="catalog-count-info" class="catalog-count-info">
                    @if(!empty($searchQuery))
                        Tìm thấy <strong>{{ $totalCount }}</strong> sản phẩm cho từ khóa "<em>{{ $searchQuery }}</em>"
                    @else
                        Hiển thị <strong>{{ $totalCount }}</strong> tác phẩm nội thất
                    @endif
                </div>
                <div class="catalog-sort-wrap">
                    <span style="font-size: 0.88rem; color: var(--text-muted);">Sắp xếp:</span>
                    <select class="sort-select" id="catalog-sort-select" onchange="
                        document.getElementById('catalog-form-sort-input').value = this.value;
                        document.getElementById('catalog-filter-form').submit();
                    ">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="bestseller" {{ $sort === 'bestseller' ? 'selected' : '' }}>Bán chạy nhất</option>
                        <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                        <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                    </select>
                </div>
            </div>

            {{-- Products Grid / Empty State --}}
            @if($products->isEmpty())
                <div class="text-center py-5 my-4 bg-white rounded-3 shadow-sm border p-4" id="catalog-empty-state">
                    <div style="width: 70px; height: 70px; margin: 0 auto 16px; border-radius: var(--radius-full); background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--text-muted);">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3 class="h5 mb-2 font-heading">Không tìm thấy sản phẩm phù hợp</h3>
                    <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                        Rất tiếc, hiện tại không có sản phẩm nào thỏa mãn toàn bộ tiêu chí lọc hoặc từ khóa bạn vừa nhập. Vui lòng thử nới rộng khoảng giá hoặc xóa bộ lọc.
                    </p>
                    <a href="{{ route('catalog.index') }}" class="btn btn-accent btn-sm">
                        <i class="fas fa-rotate-left me-1"></i> Xóa tất cả bộ lọc
                    </a>
                </div>
            @else
                <div id="catalog-products-grid" class="products-grid">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div id="catalog-pagination">
                    {{ $products->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
