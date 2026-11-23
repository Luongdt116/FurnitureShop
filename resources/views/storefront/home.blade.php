@extends('layouts.storefront')

@section('title', 'AURA LIVING - Tuyệt Tác Nội Thất Bắc Âu & Japandi Tinh Tế')

@section('content')
{{-- =======================================================================
     HERO SECTION
     ======================================================================= --}}
<section class="hero" id="store-hero-section">
    <div class="container hero-grid">
        <div>
            <span class="hero-tag"><i class="fas fa-gem"></i> Bộ sưu tập Thu - Đông 2026</span>
            <h1 class="hero-title">Định Hình Phong Cách <span>Sống Thượng Lưu</span> & Tinh Tế</h1>
            <p class="hero-desc">
                Tuyển chọn các tác phẩm nội thất gỗ sồi Bắc Mỹ FAS và da bò Ý thuộc thảo mộc. Từng đường nét được gọt giũa tỉ mỉ, kiến tạo không gian sống cân bằng, ấm cúng và đầy tính nghệ thuật.
            </p>
            <div class="hero-actions">
                <a href="{{ route('catalog.index') }}" class="btn btn-accent btn-lg" id="store-hero-btn-catalog">
                    Khám Phá Bộ Sưu Tập <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <button type="button" class="btn btn-outline btn-lg" id="store-hero-btn-consult">
                    <i class="fas fa-wand-magic-sparkles text-gold"></i> Tư Vấn Thiết Kế
                </button>
            </div>

            <div class="hero-stats">
                <div>
                    <div class="hero-stat-num">100<span>%</span></div>
                    <div class="hero-stat-label">Gỗ Tự Nhiên Xuất Khẩu</div>
                </div>
                <div>
                    <div class="hero-stat-num">5<span> Năm</span></div>
                    <div class="hero-stat-label">Bảo Hành Khung Kết Cấu</div>
                </div>
                <div>
                    <div class="hero-stat-num">15<span>.000+</span></div>
                    <div class="hero-stat-label">Căn Hộ Đã Hoàn Thiện</div>
                </div>
            </div>
        </div>

        <div class="hero-image-wrapper">
            <div class="hero-img-card">
                <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=80" alt="Living Room Nordic Design">
            </div>
            {{-- Floating Badges --}}
            <div class="floating-badge floating-badge-1">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-full); background: var(--accent-light); color: var(--accent-hover); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fas fa-couch"></i>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.9rem;">Sofa Bergamo Da Ý</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Bán chạy nhất tháng 10</div>
                </div>
            </div>
            <div class="floating-badge floating-badge-2">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-full); background: var(--status-success-bg); color: var(--status-success); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.9rem;">Bảo Hành 5 Năm</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Bảo trì trọn đời tận nơi</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =======================================================================
     VALUE PROPS RIBBON
     ======================================================================= --}}
<section class="value-props" id="store-value-props">
    <div class="container props-grid">
        <div class="prop-item">
            <div class="prop-icon"><i class="fas fa-truck-ramp-box"></i></div>
            <div>
                <div class="prop-title">Vận Chuyển & Lắp Đặt 0đ</div>
                <div class="prop-sub">Đội xe chuyên dụng giao tận nhà</div>
            </div>
        </div>
        <div class="prop-item">
            <div class="prop-icon"><i class="fas fa-certificate"></i></div>
            <div>
                <div class="prop-title">Bảo Hành Toàn Diện 5 Năm</div>
                <div class="prop-sub">Khung gỗ sồi & phụ kiện cao cấp</div>
            </div>
        </div>
        <div class="prop-item">
            <div class="prop-icon"><i class="fas fa-rotate-left"></i></div>
            <div>
                <div class="prop-title">Đổi Trả Dễ Dàng 7 Ngày</div>
                <div class="prop-sub">Thẩm định đổi mới tận căn hộ</div>
            </div>
        </div>
        <div class="prop-item">
            <div class="prop-icon"><i class="fas fa-comments"></i></div>
            <div>
                <div class="prop-title">Trợ Lý AI Tư Vấn 24/7</div>
                <div class="prop-sub">Gợi ý phối cảnh theo diện tích phòng</div>
            </div>
        </div>
    </div>
</section>

{{-- =======================================================================
     CATEGORY SHOWCASE
     ======================================================================= --}}
<section class="section" id="store-categories-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-tag">Không Gian Sống</span>
                <h2 class="section-title">Danh Mục Nội Thất Nổi Bật</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm">
                Xem Tất Cả Danh Mục &rarr;
            </a>
        </div>
        <div class="categories-grid">
            @forelse($categories as $category)
                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap">
                        <i class="fas {{ $category->image ?? 'fa-couch' }}"></i>
                    </div>
                    <div class="cat-name">{{ $category->name }}</div>
                    <div class="cat-count">{{ $category->products_count ?? $category->products()->count() }} sản phẩm</div>
                </a>
            @empty
                {{-- Fallback Default High-Fi Showcase --}}
                <a href="{{ route('catalog.index', ['room' => 'Phòng Khách']) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap"><i class="fas fa-couch"></i></div>
                    <div class="cat-name">Phòng Khách</div>
                    <div class="cat-count">18 sản phẩm</div>
                </a>
                <a href="{{ route('catalog.index', ['room' => 'Phòng Ngủ']) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap"><i class="fas fa-bed"></i></div>
                    <div class="cat-name">Phòng Ngủ</div>
                    <div class="cat-count">14 sản phẩm</div>
                </a>
                <a href="{{ route('catalog.index', ['room' => 'Phòng Ăn']) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap"><i class="fas fa-utensils"></i></div>
                    <div class="cat-name">Phòng Ăn & Bếp</div>
                    <div class="cat-count">12 sản phẩm</div>
                </a>
                <a href="{{ route('catalog.index', ['room' => 'Phòng Làm Việc']) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap"><i class="fas fa-briefcase"></i></div>
                    <div class="cat-name">Góc Làm Việc</div>
                    <div class="cat-count">9 sản phẩm</div>
                </a>
                <a href="{{ route('catalog.index', ['room' => 'Đèn Decor']) }}" class="category-card text-decoration-none text-reset">
                    <div class="cat-icon-wrap"><i class="fas fa-lightbulb"></i></div>
                    <div class="cat-name">Đèn & Decor</div>
                    <div class="cat-count">15 sản phẩm</div>
                </a>
            @endforelse
        </div>
    </div>
</section>

{{-- =======================================================================
     BEST SELLERS SECTION
     ======================================================================= --}}
<section class="section" style="background-color: #f7f4ee;" id="store-bestsellers-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-tag">Được Yêu Thích Nhất</span>
                <h2 class="section-title">Tuyệt Tác Bán Chạy</h2>
            </div>
            <a href="{{ route('catalog.index', ['sort' => 'bestseller']) }}" class="btn btn-outline btn-sm">
                Xem Bảng Xếp Hạng &rarr;
            </a>
        </div>
        <div class="products-grid" id="store-bestsellers-grid">
            @forelse($bestSellers as $product)
                <x-product-card :product="$product" />
            @empty
                {{-- Mock items if database has not been seeded yet --}}
                @php
                    $demoProducts = [
                        [
                            'id' => 1,
                            'name' => 'Sofa Bergamo Da Bò Ý Thuộc Thảo Mộc',
                            'slug' => 'sofa-bergamo-da-bo-y',
                            'category_name' => 'Sofa Phòng Khách',
                            'room_type' => 'Phòng Khách',
                            'price' => 24500000,
                            'original_price' => 28900000,
                            'discount_percent' => 15,
                            'dimensions' => '220 x 95 x 85 cm',
                            'wood_material' => 'Gỗ sồi Bắc Mỹ FAS, Da bò Ý Mastrotto',
                            'rating' => 4.9,
                            'review_count' => 38,
                            'is_bestseller' => true,
                            'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Bàn Ăn Gỗ Sồi OAK Trắng Tự Nhiên Oslo',
                            'slug' => 'ban-an-go-soi-oak-oslo',
                            'category_name' => 'Bàn Ăn',
                            'room_type' => 'Phòng Ăn',
                            'price' => 14800000,
                            'original_price' => 16500000,
                            'discount_percent' => 10,
                            'dimensions' => '180 x 85 x 75 cm',
                            'wood_material' => 'Gỗ sồi trắng nguyên khối FAS',
                            'rating' => 4.8,
                            'review_count' => 24,
                            'is_bestseller' => true,
                            'image' => 'https://images.unsplash.com/photo-1530018607912-eff2daa1bac4?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Giường Ngủ Bắc Âu Nordic Calm King Size',
                            'slug' => 'giuong-ngu-nordic-calm-king',
                            'category_name' => 'Giường Ngủ',
                            'room_type' => 'Phòng Ngủ',
                            'price' => 19200000,
                            'original_price' => 22000000,
                            'discount_percent' => 12,
                            'dimensions' => '200 x 220 x 105 cm',
                            'wood_material' => 'Khung gỗ sồi, nệm bọc vải nỉ Bỉ',
                            'rating' => 5.0,
                            'review_count' => 19,
                            'is_bestseller' => false,
                            'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80',
                        ],
                    ];
                @endphp
                @foreach($demoProducts as $dp)
                    <x-product-card :product="$dp" />
                @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- =======================================================================
     FLASH SALE / SPECIAL OFFERS
     ======================================================================= --}}
<section class="section" id="store-flashsale-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-tag"><i class="fas fa-bolt text-warning"></i> Giờ Vàng Ưu Đãi</span>
                <h2 class="section-title">Ưu Đãi Đặc Biệt Tuần Này</h2>
            </div>
            <div style="font-size: 0.9rem; font-weight: 700; color: var(--accent);">
                <i class="far fa-clock"></i> Kết thúc trong: <span style="background: var(--primary); color: #fff; padding: 4px 8px; border-radius: 4px;">02 Ngày 14:30:45</span>
            </div>
        </div>
        <div class="products-grid" id="store-flashsale-grid">
            @forelse($flashSales as $product)
                <x-product-card :product="$product" />
            @empty
                @php
                    $flashDemo = [
                        [
                            'id' => 4,
                            'name' => 'Tủ Quần Áo Cánh Kính Đèn LED Tokyo Japandi',
                            'slug' => 'tu-quan-ao-canh-kinh-tokyo',
                            'category_name' => 'Tủ Áo',
                            'room_type' => 'Phòng Ngủ',
                            'price' => 26800000,
                            'original_price' => 32000000,
                            'discount_percent' => 16,
                            'dimensions' => '240 x 60 x 220 cm',
                            'wood_material' => 'Khung nhôm Anodize, kính xám khói',
                            'rating' => 4.9,
                            'review_count' => 15,
                            'is_bestseller' => true,
                            'image' => 'https://images.unsplash.com/photo-1558997519-83ea9252edf8?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'id' => 5,
                            'name' => 'Bàn Trà Đôi Mặt Đá Marble Cao Cấp Luna',
                            'slug' => 'ban-tra-doi-mat-da-luna',
                            'category_name' => 'Bàn Trà',
                            'room_type' => 'Phòng Khách',
                            'price' => 6900000,
                            'original_price' => 8500000,
                            'discount_percent' => 18,
                            'dimensions' => '80 x 80 x 42 cm',
                            'wood_material' => 'Mặt đá cẩm thạch trắng, chân inox mạ PVD',
                            'rating' => 4.7,
                            'review_count' => 29,
                            'is_bestseller' => false,
                            'image' => 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'id' => 6,
                            'name' => 'Ghế Thư Giãn Lounge Chair Da Cognac Oxford',
                            'slug' => 'ghe-thu-gian-oxford-cognac',
                            'category_name' => 'Ghế Thư Giãn',
                            'room_type' => 'Phòng Khách',
                            'price' => 11500000,
                            'original_price' => 13500000,
                            'discount_percent' => 15,
                            'dimensions' => '85 x 88 x 82 cm',
                            'wood_material' => 'Khung gỗ óc chó FAS, Da bò sáp',
                            'rating' => 5.0,
                            'review_count' => 41,
                            'is_bestseller' => true,
                            'image' => 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=800&q=80',
                        ],
                    ];
                @endphp
                @foreach($flashDemo as $fd)
                    <x-product-card :product="$fd" />
                @endforeach
            @endforelse
        </div>
    </div>
</section>
@endsection
