<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Display the storefront home page.
     */
    public function home(): View
    {
        // 1. Categories
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        // 2. Best Sellers
        $bestSellers = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->orderByDesc('discount_percent')
            ->take(6)
            ->get();

        // 3. Flash Sale (Special Offers)
        $flashSales = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('discount_percent', '>', 0)
            ->orderByDesc('discount_percent')
            ->take(4)
            ->get();

        // Graceful fallback for demo if database is not yet seeded
        if ($categories->isEmpty()) {
            $categories = $this->getFallbackCategories();
        }
        if ($bestSellers->isEmpty()) {
            $bestSellers = $this->getFallbackProducts()->take(6);
        }
        if ($flashSales->isEmpty()) {
            $flashSales = $this->getFallbackProducts()->where('discount_percent', '>', 0)->take(4);
        }

        return view('storefront.home', compact('categories', 'bestSellers', 'flashSales'));
    }

    /**
     * Display the product catalog with filtering, sorting, price range and pagination.
     */
    public function catalog(Request $request): View
    {
        $categories = Category::query()->where('is_active', true)->withCount('products')->orderBy('sort_order')->get();
        if ($categories->isEmpty()) {
            $categories = $this->getFallbackCategories();
        }

        $query = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true);

        $selectedCategory = $request->query('category', 'all');
        $selectedRoom = $request->query('room', 'all');
        $selectedMaterial = $request->query('material', 'all');
        $maxPrice = $request->query('max_price', 35000000);
        $searchQuery = trim($request->query('q', ''));
        $sort = $request->query('sort', 'newest');

        // Filter by Category
        if ($selectedCategory !== 'all' && !empty($selectedCategory)) {
            $query->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('slug', $selectedCategory)->orWhere('id', $selectedCategory);
            });
        }

        // Filter by Room
        if ($selectedRoom !== 'all' && !empty($selectedRoom)) {
            $query->where(function ($q) use ($selectedRoom) {
                $q->where('dimensions', 'like', "%{$selectedRoom}%")
                  ->orWhere('description', 'like', "%{$selectedRoom}%")
                  ->orWhere('name', 'like', "%{$selectedRoom}%");
            });
        }

        // Filter by Material
        if ($selectedMaterial !== 'all' && !empty($selectedMaterial)) {
            $query->where('material', 'like', "%{$selectedMaterial}%");
        }

        // Filter by Price range
        if ($maxPrice && $maxPrice < 35000000) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        // Search Query
        if (!empty($searchQuery)) {
            $searchTerm = "%{$searchQuery}%";
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('sku', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('material', 'like', $searchTerm);
            });
        }

        // Sorting
        switch ($sort) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'bestseller':
                $query->orderByDesc('discount_percent');
                break;
            case 'newest':
            default:
                $query->orderByDesc('id');
                break;
        }

        $products = $query->paginate(6)->withQueryString();

        // If database is empty, provide fallback paginator
        if ($products->total() === 0 && Product::count() === 0) {
            $fallbackList = $this->getFallbackProducts();

            // Apply filters to fallback
            if ($selectedCategory !== 'all' && !empty($selectedCategory)) {
                $fallbackList = $fallbackList->filter(function ($p) use ($selectedCategory) {
                    return ($p->category_slug ?? '') === $selectedCategory || (string)($p->category_id ?? '') === (string)$selectedCategory;
                });
            }
            if ($selectedRoom !== 'all') {
                $fallbackList = $fallbackList->filter(fn($p) => ($p->room_type ?? '') === $selectedRoom || str_contains($p->name, $selectedRoom));
            }
            if ($selectedMaterial !== 'all') {
                $fallbackList = $fallbackList->filter(fn($p) => str_contains(mb_strtolower($p->material ?? ''), mb_strtolower($selectedMaterial)));
            }
            if ($maxPrice && $maxPrice < 35000000) {
                $fallbackList = $fallbackList->filter(fn($p) => $p->price <= $maxPrice);
            }
            if (!empty($searchQuery)) {
                $fallbackList = $fallbackList->filter(fn($p) => str_contains(mb_strtolower($p->name), mb_strtolower($searchQuery)) || str_contains(mb_strtolower($p->sku), mb_strtolower($searchQuery)));
            }

            // Fallback Sorting
            if ($sort === 'price-asc') {
                $fallbackList = $fallbackList->sortBy('price');
            } elseif ($sort === 'price-desc') {
                $fallbackList = $fallbackList->sortByDesc('price');
            } elseif ($sort === 'bestseller') {
                $fallbackList = $fallbackList->sortByDesc('discount_percent');
            }

            $perPage = 6;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $fallbackList->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $products = new LengthAwarePaginator($currentItems, $fallbackList->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);
        }

        $totalCount = $products->total();

        return view('storefront.catalog', compact(
            'products',
            'categories',
            'selectedCategory',
            'selectedRoom',
            'selectedMaterial',
            'maxPrice',
            'searchQuery',
            'sort',
            'totalCount'
        ));
    }

    /**
     * Search page with query, suggestions and empty state.
     */
    public function search(Request $request): View
    {
        return $this->catalog($request);
    }

    /**
     * Dynamic AJAX Search Suggestions for live dropdown.
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $query = trim($request->query('q', ''));
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $searchTerm = "%{$query}%";
        $results = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('sku', 'like', $searchTerm)
                  ->orWhere('material', 'like', $searchTerm);
            })
            ->take(5)
            ->get();

        if ($results->isEmpty() && Product::count() === 0) {
            $fallback = $this->getFallbackProducts()
                ->filter(fn($p) => str_contains(mb_strtolower($p->name), mb_strtolower($query)) || str_contains(mb_strtolower($p->sku), mb_strtolower($query)))
                ->take(5);

            $data = $fallback->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => number_format($p->price, 0, ',', '.') . '₫',
                    'image' => $p->images[0] ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80',
                    'url' => route('product.detail', $p->slug),
                ];
            })->values();

            return response()->json($data);
        }

        $data = $results->map(function ($p) {
            $primaryImg = $p->images->firstWhere('is_primary', true) ?? $p->images->first();
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => number_format($p->price, 0, ',', '.') . '₫',
                'image' => $primaryImg ? asset($primaryImg->path) : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80',
                'url' => route('product.detail', $p->slug),
            ];
        });

        return response()->json($data);
    }

    /**
     * Display product detail page with images, specs, warranty, reviews and related products.
     */
    public function productDetail(string $slug): View
    {
        $product = Product::query()
            ->with(['category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$product) {
            // Find from fallback if database has not been seeded
            $fallback = $this->getFallbackProducts()->first(fn($p) => $p->slug === $slug || (string)$p->id === $slug);
            if ($fallback) {
                $product = $fallback;
                $relatedProducts = $this->getFallbackProducts()->where('id', '!=', $product->id)->take(4);
                $reviews = $this->getFallbackReviews();
                return view('storefront.product-detail', compact('product', 'relatedProducts', 'reviews'));
            }
            abort(404, 'Không tìm thấy sản phẩm nội thất yêu cầu.');
        }

        $relatedProducts = Product::query()
            ->with(['category', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()->with(['category', 'images'])->where('id', '!=', $product->id)->take(4)->get();
        }

        $reviews = $product->reviews ?? collect();
        if ($reviews->isEmpty()) {
            $reviews = $this->getFallbackReviews();
        }

        return view('storefront.product-detail', compact('product', 'relatedProducts', 'reviews'));
    }

    /**
     * Helper: Fallback realistic high-fi Categories
     */
    private function getFallbackCategories(): Collection
    {
        return collect([
            (object)['id' => 1, 'name' => 'Nội Thất Phòng Khách', 'slug' => 'phong-khach', 'image' => 'fa-couch', 'products_count' => 18],
            (object)['id' => 2, 'name' => 'Nội Thất Phòng Ngủ', 'slug' => 'phong-ngu', 'image' => 'fa-bed', 'products_count' => 14],
            (object)['id' => 3, 'name' => 'Phòng Ăn & Nhà Bếp', 'slug' => 'phong-an', 'image' => 'fa-utensils', 'products_count' => 12],
            (object)['id' => 4, 'name' => 'Bàn Ghế Làm Việc', 'slug' => 'lam-viec', 'image' => 'fa-briefcase', 'products_count' => 9],
            (object)['id' => 5, 'name' => 'Đèn & Trang Trí Decor', 'slug' => 'trang-tri', 'image' => 'fa-lightbulb', 'products_count' => 15],
        ]);
    }

    /**
     * Helper: Fallback realistic high-fi Products
     */
    private function getFallbackProducts(): Collection
    {
        return collect([
            (object)[
                'id' => 1,
                'sku' => 'SF-BG-01',
                'name' => 'Sofa Da Bò Ý Bergamo 3 Chỗ Cao Cấp',
                'slug' => 'sofa-da-bo-y-bergamo-3-cho',
                'category_name' => 'Nội Thất Phòng Khách',
                'category_slug' => 'phong-khach',
                'room_type' => 'Phòng Khách',
                'price' => 24500000,
                'original_price' => 28900000,
                'discount_percent' => 15,
                'stock' => 12,
                'dimensions' => '220 x 95 x 85 cm',
                'material' => 'Khung gỗ thông Nga, Da bò Ý Mastrotto tự nhiên',
                'color' => 'Nâu Cognac, Đen Espresso, Kem Ivory',
                'origin' => 'Nhập khẩu nguyên chiếc Italy',
                'warranty_period' => '5 năm khung & da',
                'rating' => 4.9,
                'review_count' => 38,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Sofa Bergamo sở hữu đường nét tối giản tinh tế theo phong cách Nordic Luxury. Da bò thật mềm mại, đệm mút D40 chống lún xẹp tuyệt đối kết hợp lông vũ tạo cảm giác êm ái vượt trội.',
            ],
            (object)[
                'id' => 2,
                'sku' => 'BA-ND-02',
                'name' => 'Bộ Bàn Ăn Gỗ Sồi Bắc Mỹ Nordic Luxe (Kèm 6 Ghế)',
                'slug' => 'bo-ban-an-go-soi-bac-my-nordic-luxe',
                'category_name' => 'Phòng Ăn & Nhà Bếp',
                'category_slug' => 'phong-an',
                'room_type' => 'Phòng Ăn',
                'price' => 18900000,
                'original_price' => 22000000,
                'discount_percent' => 14,
                'stock' => 8,
                'dimensions' => 'Bàn: 180 x 85 x 75 cm | Ghế: 48 x 52 x 82 cm',
                'material' => '100% Gỗ sồi trắng Bắc Mỹ (White Oak), Ghế nệm nỉ Bỉ',
                'color' => 'Gỗ tự nhiên (Natural), Nâu hạt dẻ (Walnut)',
                'origin' => 'Gia công xuất khẩu Bắc Âu',
                'warranty_period' => '3 năm chính hãng',
                'rating' => 4.8,
                'review_count' => 24,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Bộ bàn ăn Nordic Luxe được bo tròn cạnh an toàn cho gia đình có trẻ nhỏ. Bề mặt gỗ sồi sơn lót UV 5 lớp chống trầy xước, chịu nhiệt độ nước sôi tối ưu.',
            ],
            (object)[
                'id' => 3,
                'sku' => 'GN-SC-03',
                'name' => 'Giường Ngủ Bọc Nỉ Phong Cách Scandinavian King Size',
                'slug' => 'giuong-ngu-boc-ni-scandinavian-king-size',
                'category_name' => 'Nội Thất Phòng Ngủ',
                'category_slug' => 'phong-ngu',
                'room_type' => 'Phòng Ngủ',
                'price' => 16500000,
                'original_price' => 19500000,
                'discount_percent' => 15,
                'stock' => 15,
                'dimensions' => '180 x 200 x 110 cm (Lòng đệm 1m8)',
                'material' => 'Khung gỗ bạch dương, Vải nỉ nhung Bỉ kháng khuẩn',
                'color' => 'Xám Khói, Xanh Rêu Pastel, Kem Linen',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '5 năm khung giường',
                'rating' => 4.9,
                'review_count' => 19,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1540518614846-7ede433c4ef0?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Thiết kế đầu giường gợn sóng tinh tế tạo điểm tựa lưng êm ái khi đọc sách. Khung nan cong đàn hồi thông minh trợ lực tối đa cho nệm lò xo túi cao cấp.',
            ],
            (object)[
                'id' => 4,
                'sku' => 'BLV-WL-04',
                'name' => 'Bàn Làm Việc Giám Đốc Gỗ Óc Chó Walnut Elegance',
                'slug' => 'ban-lam-viec-giam-doc-go-oc-cho-walnut',
                'category_name' => 'Bàn Ghế Làm Việc',
                'category_slug' => 'lam-viec',
                'room_type' => 'Phòng Làm Việc',
                'price' => 14200000,
                'original_price' => 16800000,
                'discount_percent' => 15,
                'stock' => 6,
                'dimensions' => '160 x 75 x 75 cm',
                'material' => 'Gỗ óc chó Bắc Mỹ FAS 100%, lót da vi sợi',
                'color' => 'Nâu Trầm Óc Chó Tự Nhiên',
                'origin' => 'Xưởng mộc thủ công AURA',
                'warranty_period' => '5 năm toàn phần',
                'rating' => 4.9,
                'review_count' => 15,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Đỉnh cao phong cách làm việc của doanh nhân thành đạt. Thiết kế giấu dây điện thông minh, tích hợp sạc không dây 15W âm mặt bàn và ngăn kéo khóa mã số.',
            ],
            (object)[
                'id' => 5,
                'sku' => 'GH-EM-05',
                'name' => 'Ghế Thư Giãn Đọc Sách Eames Lounge Chair Kèm Đôn',
                'slug' => 'ghe-thu-gian-eames-lounge-chair',
                'category_name' => 'Nội Thất Phòng Khách',
                'category_slug' => 'phong-khach',
                'room_type' => 'Phòng Khách',
                'price' => 15900000,
                'original_price' => 18500000,
                'discount_percent' => 14,
                'stock' => 10,
                'dimensions' => 'Ghế: 84 x 85 x 84 cm | Đôn: 65 x 54 x 44 cm',
                'material' => 'Vỏ gỗ ép uốn nhiệt 7 lớp veneer óc chó, Da bò Ý Semi-Aniline',
                'color' => 'Đen Cổ Điển, Nâu Amber, Trắng Sữa',
                'origin' => 'Nhập khẩu Hong Kong chuẩn 1:1',
                'warranty_period' => '3 năm',
                'rating' => 5.0,
                'review_count' => 42,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1580481077195-c22ae24269d5?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Biểu tượng kinh điển của thiết kế thế kỷ 20. Chân xoay hợp kim nhôm đúc chịu tải trọng 200kg, ngả lưng chuẩn 15 độ nâng niu cột sống tuyệt đối.',
            ],
            (object)[
                'id' => 6,
                'sku' => 'DT-GL-06',
                'name' => 'Đèn Thả Bàn Ăn Thủy Tinh Nghệ Thuật Brass & Glass',
                'slug' => 'den-tha-ban-an-thuy-tinh-brass-glass',
                'category_name' => 'Đèn & Trang Trí Decor',
                'category_slug' => 'trang-tri',
                'room_type' => 'Phòng Ăn',
                'price' => 3600000,
                'original_price' => 4500000,
                'discount_percent' => 20,
                'stock' => 25,
                'dimensions' => 'Đường kính 120 cm, dây treo tùy chỉnh 30 - 120 cm',
                'material' => 'Thân kim loại đồng thau mạ vàng PVD, Thủy tinh khói thổi thủ công',
                'color' => 'Đồng Mạ Vàng, Đen Nhám',
                'origin' => 'Hàn Quốc',
                'warranty_period' => '2 năm nguồn và chao',
                'rating' => 4.7,
                'review_count' => 16,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Tạo điểm nhấn lung linh cho phòng ăn sang trọng với 6 bóng LED tiết kiệm điện CRI > 90 phản chiếu ánh sáng chân thực ấm cúng cho bữa cơm sum vầy.',
            ],
            (object)[
                'id' => 7,
                'sku' => 'TQ-CK-07',
                'name' => 'Tủ Quần Áo Cánh Kính Cường Lực Khung Nhôm Anode LED',
                'slug' => 'tu-quan-ao-canh-kinh-cuong-luc-khung-nhom',
                'category_name' => 'Nội Thất Phòng Ngủ',
                'category_slug' => 'phong-ngu',
                'room_type' => 'Phòng Ngủ',
                'price' => 28500000,
                'original_price' => 33000000,
                'discount_percent' => 13,
                'stock' => 5,
                'dimensions' => '240 x 60 x 260 cm (4 cánh)',
                'material' => 'Thùng MDF lõi xanh chống ẩm An Cường, Kính xám khói cường lực 5mm',
                'color' => 'Thùng Gỗ Xám Titan, Thùng Gỗ Óc Chó',
                'origin' => 'Thiết kế thi công tại xưởng AURA',
                'warranty_period' => '5 năm phụ kiện & kính',
                'rating' => 4.9,
                'review_count' => 11,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1558997519-83ea9252edf8?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Biến phòng ngủ thành showroom thời trang triệu đô với hệ cảm biến chạm tay mở đèn LED ray tự động thông minh và ngăn kéo phụ kiện chia trang sức bọc nhung.',
            ],
            (object)[
                'id' => 8,
                'sku' => 'BT-MD-08',
                'name' => 'Bàn Trà Mặt Đá Ceramic Ý Chống Trầy Khung Mạ PVD',
                'slug' => 'ban-tra-mat-da-ceramic-y-chong-tray',
                'category_name' => 'Nội Thất Phòng Khách',
                'category_slug' => 'phong-khach',
                'room_type' => 'Phòng Khách',
                'price' => 7800000,
                'original_price' => 9200000,
                'discount_percent' => 15,
                'stock' => 18,
                'dimensions' => 'Bộ đôi lồng ghép: Bàn lớn D80 x H45cm, Bàn nhỏ D60 x H40cm',
                'material' => 'Khung thép carbon mạ PVD vàng, Đá Ceramic chống nhiệt 1000°C',
                'color' => 'Trắng Vân Mây, Đen Tia Chớp',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '2 năm',
                'rating' => 4.8,
                'review_count' => 29,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Bàn trà đôi có thể kéo linh hoạt ra vào tiết kiệm không gian. Mặt đá phiến chống thấm ố trà cà phê, dễ dàng lau chùi chỉ với khăn ẩm.',
            ],
            (object)[
                'id' => 9,
                'sku' => 'BV-OC-09',
                'name' => 'Bàn Làm Việc Gỗ Óc Chó Tự Nhiên Director Desk',
                'slug' => 'ban-lam-viec-go-oc-cho-tu-nhien-director-desk',
                'category_name' => 'Bàn Ghế Làm Việc',
                'category_slug' => 'lam-viec',
                'room_type' => 'Phòng Làm Việc',
                'price' => 15500000,
                'original_price' => 18000000,
                'discount_percent' => 14,
                'stock' => 7,
                'dimensions' => '160 x 80 x 75 cm',
                'material' => '100% Gỗ óc chó Bắc Mỹ (Walnut) tự nhiên, phụ kiện ray Hafele',
                'color' => 'Nâu Óc Chó Tự Nhiên',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '5 năm',
                'rating' => 4.9,
                'review_count' => 15,
                'is_bestseller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Bàn giám đốc thiết kế đường cong tinh tế, hộc tủ tích hợp khóa từ thông minh, hỗ trợ giấu dây và hoàn thiện dầu lau gốc thực vật sang trọng.',
            ],
            (object)[
                'id' => 10,
                'sku' => 'GT-EM-10',
                'name' => 'Ghế Bành Thư Giãn Nordic Single Lounge Chair',
                'slug' => 'ghe-banh-thu-gian-nordic-single-lounge-chair',
                'category_name' => 'Nội Thất Phòng Khách',
                'category_slug' => 'phong-khach',
                'room_type' => 'Phòng Khách',
                'price' => 8900000,
                'original_price' => 10500000,
                'discount_percent' => 15,
                'stock' => 11,
                'dimensions' => '82 x 78 x 85 cm',
                'material' => 'Khung gỗ sồi tần bì tự nhiên, Đệm nỉ len cao cấp Bỉ',
                'color' => 'Vàng Mù Tạt, Xanh Rêu, Xám Ghi',
                'origin' => 'Xuất khẩu Bắc Âu',
                'warranty_period' => '3 năm',
                'rating' => 4.8,
                'review_count' => 18,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1580481077195-c3a821a58875?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Mẫu ghế bành đơn điểm nhấn tuyệt vời cho phòng khách hoặc góc đọc sách riêng tư. Nâng đỡ thắt lưng tự nhiên, vải bọc thoáng khí 4 mùa.',
            ],
            (object)[
                'id' => 11,
                'sku' => 'DC-AR-11',
                'name' => 'Đèn Cây Đứng Phòng Khách Nordic Marble Brass Arc',
                'slug' => 'den-cay-dung-phong-khach-nordic-marble',
                'category_name' => 'Đèn & Trang Trí Decor',
                'category_slug' => 'trang-tri',
                'room_type' => 'Phòng Khách',
                'price' => 4200000,
                'original_price' => 4900000,
                'discount_percent' => 14,
                'stock' => 20,
                'dimensions' => 'Cần vươn 150 cm, Cao 205 cm, Đế đá phi 40 cm',
                'material' => 'Đế đá cẩm thạch trắng tự nhiên Carrara, Thân kim loại đồng xước PVD',
                'color' => 'Vàng Đồng Xước, Đen Nhám',
                'origin' => 'Nhập khẩu cao cấp',
                'warranty_period' => '2 năm',
                'rating' => 4.8,
                'review_count' => 19,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Đèn sàn cần cong vươn dài với chân đế đá hoa cương đầm chắc, tạo luồng sáng êm dịu bao trùm trọn vẹn khu vực sofa trung tâm.',
            ],
            (object)[
                'id' => 12,
                'sku' => 'TG-SK-12',
                'name' => 'Tủ Đầu Giường 2 Ngăn Kéo Gỗ Sồi Bắc Mỹ Tự Nhiên',
                'slug' => 'tu-dau-giuong-2-ngan-keo-go-soi-bac-my',
                'category_name' => 'Nội Thất Phòng Ngủ',
                'category_slug' => 'phong-ngu',
                'room_type' => 'Phòng Ngủ',
                'price' => 3800000,
                'original_price' => 4500000,
                'discount_percent' => 15,
                'stock' => 14,
                'dimensions' => '50 x 42 x 52 cm',
                'material' => '100% Gỗ sồi trắng Bắc Mỹ, Ray trượt giảm chấn Blum',
                'color' => 'Gỗ Tự Nhiên, Nâu Hạt Dẻ',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '3 năm',
                'rating' => 4.9,
                'review_count' => 27,
                'is_bestseller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1532372320572-cda25653a26d?auto=format&fit=crop&w=1000&q=80',
                ],
                'description' => 'Thiết kế bo tròn viền tinh mỹ, tay nắm âm giấu kín, bề mặt hoàn thiện dầu lau gốc thực vật an toàn cho giấc ngủ của gia chủ.',
            ],
        ]);
    }

    /**
     * Helper: Fallback realistic Reviews
     */
    private function getFallbackReviews(): Collection
    {
        return collect([
            (object)[
                'user_name' => 'Phạm Hoàng Nam',
                'user_avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80',
                'rating' => 5,
                'title' => 'Chất lượng da bò tuyệt hảo, đóng gói cực kỳ cẩn thận!',
                'comment' => 'Đã nhận được hàng sau 3 ngày đặt. Đội ngũ giao vận rất chuyên nghiệp, khiêng lên tận tầng 18 chung cư và hỗ trợ bọc chân bảo vệ sàn gỗ. Da rất mềm và thơm mùi da tự nhiên.',
                'created_at' => '15/10/2026',
            ],
            (object)[
                'user_name' => 'Vũ Thị Minh Hằng',
                'user_avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80',
                'rating' => 5,
                'title' => 'Đẹp hơn cả trong ảnh showroom, rất ưng ý!',
                'comment' => 'Màu sắc thực tế bên ngoài rất ấm cúng và sang trọng. Khung gỗ sồi chắc nịch không một tiếng cót két. Chắc chắn sẽ tiếp tục ủng hộ AURA LIVING khi hoàn thiện phòng ngủ.',
                'created_at' => '02/11/2026',
            ],
            (object)[
                'user_name' => 'Đặng Tuấn Anh',
                'user_avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=100&q=80',
                'rating' => 5,
                'title' => 'Tư vấn nhiệt tình, chính sách bảo hành 5 năm an tâm tuyệt đối.',
                'comment' => 'Được bạn bè làm kiến trúc sư giới thiệu thương hiệu này. Thật sự đường nét gia công mộng gỗ rất khít và chuẩn mực châu Âu.',
                'created_at' => '10/11/2026',
            ],
        ]);
    }

}
