<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotService
{
    private const CATEGORIES = [
        'sofa' => ['sofa', 'ghe sofa'],
        'giuong' => ['giuong', 'giuong ngu'],
        'ban an' => ['ban an', 'ban cafe', 'ban tra'],
        'tu' => ['tu ao', 'tu ke', 'tu giay'],
        'den' => ['den ban', 'den trang tri'],
        'ghe' => ['ghe an', 'ghe don'],
    ];

    private const MATERIALS = [
        'go soi' => ['go soi'],
        'go oc cho' => ['oc cho'],
        'go tu nhien' => ['go tu nhien'],
        'da' => ['da that', 'da cong nghiep'],
        'vai' => ['vai', 'ni'],
        'kim loai' => ['kim loai', 'thep'],
        'may tre' => ['may', 'tre'],
    ];

    private const COLORS = ['trang', 'den', 'nau', 'xam', 'be', 'kem', 'xanh', 'do', 'vang'];

    private const SCOPE_TERMS = [
        'noi that', 'san pham', 'mua hang', 'dat hang', 'don hang', 'huy don', 'doi tra',
        'hoan tien', 'giao hang', 'van chuyen', 'lap dat', 'thanh toan', 'bao hanh', 'ton kho', 'con hang',
        'chat lieu', 'kich thuoc', 'mau sac', 'phong khach', 'phong ngu', 'phong bep', 'phong an',
        'ngan sach', 'phong cach', 'chinh sach', 'gia', 'sofa', 'giuong', 'ban an', 'tu ao', 'den ban', 'ghe an',
        'tra cuu don', 'theo doi don',
        'aura living', 'furnitureshop',
    ];

    /** @return array{response:string, products?:array, suggestedQuestions?:array} */
    public function respond(string $message, ?Collection $previousUserMessages = null): array
    {
        $previousUserMessages ??= collect();
        $normalized = $this->normalize($message);
        $context = $this->normalize(implode(' ', $previousUserMessages->take(-5)->all()) . ' ' . $message);

        if ($this->isExplicitlyOffTopic($normalized)
            || (! $this->isInStoreScope($normalized) && ! $this->isInStoreScope($context))) {
            return [
                'response' => 'Mình chỉ hỗ trợ tư vấn sản phẩm và dịch vụ của AURA LIVING. Bạn muốn hỏi về sản phẩm, giá, chất liệu, giao hàng, thanh toán hay đổi trả?',
                'suggestedQuestions' => ['Gợi ý sofa cho phòng khách', 'Chính sách giao hàng thế nào?', 'Sản phẩm có bảo hành bao lâu?'],
            ];
        }

        if ($this->hasAny($normalized, ['doi tra', 'hoan tien', 'tra hang'])) {
            return [
                'response' => 'Trang chính sách hiện nêu thời hạn 7 ngày cho sản phẩm có lỗi kỹ thuật gia công hoặc hư hỏng khi vận chuyển; khách có thể yêu cầu đổi sản phẩm hoặc hoàn tiền qua phương thức thanh toán ban đầu. Yêu cầu cụ thể sẽ được cửa hàng kiểm tra.',
                'suggestedQuestions' => ['Làm sao gửi yêu cầu đổi trả?', 'Tôi có thể kiểm tra trạng thái yêu cầu ở đâu?'],
            ];
        }

        if ($this->hasAny($normalized, ['giao hang', 'van chuyen', 'lap dat', 'phi ship'])) {
            return [
                'response' => 'Thông tin giao hàng trên website hiện chưa hoàn toàn thống nhất: trang điều khoản ghi miễn phí vận chuyển tận căn hộ, còn thanh thông báo đầu trang ghi áp dụng cho đơn từ 3.000.000₫. Mình chưa thể xác nhận điều kiện cụ thể hơn từ dữ liệu hiện có.',
                'suggestedQuestions' => ['Chính sách đổi trả thế nào?', 'Tìm sản phẩm đang còn hàng'],
            ];
        }

        if ($this->hasAny($normalized, ['thanh toan', 'tra tien', 'phuong thuc thanh toan'])) {
            return [
                'response' => 'Hệ thống hiện khai báo các phương thức COD, VNPay, VietQR và online_demo (phương thức thử nghiệm). Tùy chọn thực tế sẽ được hiển thị tại bước thanh toán.',
                'suggestedQuestions' => ['Tôi muốn tìm sản phẩm', 'Làm sao kiểm tra đơn hàng?'],
            ];
        }

        if ($this->hasAny($normalized, ['bao hanh']) && $this->categoryFor($normalized) === null) {
            return [
                'response' => 'Trang điều khoản hiện ghi bảo hành kết cấu 5 năm cho sản phẩm gỗ tự nhiên. Thời hạn bảo hành cụ thể của từng sản phẩm được lấy từ thông tin sản phẩm; bạn cho mình tên hoặc loại sản phẩm để tra đúng hơn nhé.',
                'suggestedQuestions' => ['Bảo hành sofa gỗ sồi thế nào?', 'Tìm sản phẩm đang còn hàng'],
            ];
        }

        if ($this->hasAny($normalized, ['tra cuu don', 'theo doi don', 'don hang cua toi'])) {
            return [
                'response' => 'Bạn có thể đăng nhập rồi mở mục Đơn hàng để xem thanh toán, trạng thái và tracking. Mình không truy cập thông tin đơn cá nhân trong khung chat này.',
                'suggestedQuestions' => ['Tôi muốn hủy đơn hàng', 'Chính sách đổi trả thế nào?'],
            ];
        }

        if ($this->hasAny($normalized, ['dat hang', 'mua hang'])) {
            return [
                'response' => 'Bạn có thể mở sản phẩm để xem thông tin, thêm vào giỏ hàng rồi làm theo bước thanh toán trên website. Nếu cho mình biết loại nội thất hoặc ngân sách, mình sẽ tìm sản phẩm phù hợp.',
                'suggestedQuestions' => ['Gợi ý sofa cho phòng khách', 'Tìm bàn ăn đang còn hàng'],
            ];
        }

        if ($this->hasAny($normalized, ['chinh sach'])) {
            return [
                'response' => 'Mình có thể hỗ trợ chính sách đổi trả, bảo hành, giao hàng và thanh toán. Bạn muốn xem nội dung nào?',
                'suggestedQuestions' => ['Chính sách đổi trả thế nào?', 'Giao hàng có miễn phí không?', 'Có những cách thanh toán nào?'],
            ];
        }

        if ($this->hasAny($normalized, ['huy don', 'huy mua'])) {
            return [
                'response' => 'Theo luồng hiện tại, khách có thể gửi yêu cầu hủy khi đơn đang chuẩn bị hoặc chờ vận chuyển. Đơn thanh toán online quá hạn được hệ thống xử lý riêng; bạn có thể mở chi tiết đơn để gửi yêu cầu nếu còn đủ điều kiện.',
                'suggestedQuestions' => ['Xem đơn hàng của tôi', 'Tôi muốn xem chính sách đổi trả'],
            ];
        }

        $query = $context;
        if ($this->isRecommendationRequest($context)) {
            $missingDetail = $this->nextMissingRecommendationDetail($context);
            if ($missingDetail !== null) {
                return [
                    'response' => $missingDetail,
                    'suggestedQuestions' => ['Ngân sách dưới 15 triệu', 'Phòng khách nhỏ', 'Ưu tiên gỗ tự nhiên'],
                ];
            }
        }

        $products = $this->findProducts($query);
        if ($products->isEmpty()) {
            $alternatives = $this->findAlternatives($query);

            return [
                'response' => $alternatives->isEmpty()
                    ? 'Mình chưa tìm thấy sản phẩm phù hợp và còn hàng trong danh mục hiện tại. Bạn có thể nới ngân sách hoặc cho mình biết loại nội thất khác muốn tìm.'
                    : 'Mình chưa thấy sản phẩm còn hàng khớp toàn bộ tiêu chí. Đây là một số lựa chọn khác đang còn hàng để bạn tham khảo.',
                ...($alternatives->isNotEmpty() ? ['products' => $this->productCards($alternatives)] : []),
                'suggestedQuestions' => ['Có lựa chọn nào giá thấp hơn không?', 'Có màu hoặc chất liệu khác không?'],
            ];
        }

        return [
            'response' => 'Mình tìm được một số sản phẩm đang còn hàng phù hợp với thông tin bạn đưa ra. Giá, thông số và tình trạng hàng dưới đây lấy từ danh mục hiện tại của cửa hàng.',
            'products' => $this->productCards($products),
            'suggestedQuestions' => ['Có màu khác không?', 'Kích thước sản phẩm là bao nhiêu?', 'Sản phẩm được bảo hành thế nào?'],
        ];
    }

    private function isInStoreScope(string $message): bool
    {
        return $this->hasAny($message, self::SCOPE_TERMS);
    }

    private function isExplicitlyOffTopic(string $message): bool
    {
        return $this->hasAny($message, [
            'hom nay la ngay may', 'bay gio la may gio', 'thoi tiet hom nay', 'ke chuyen cuoi',
            'ket qua bong da', 'bitcoin hom nay', 'gia vang hom nay', 'ai la tong thong',
        ]) && ! $this->hasAny($message, ['noi that', 'san pham', 'aura living', 'furnitureshop']);
    }

    private function isRecommendationRequest(string $message): bool
    {
        return $this->hasAny($message, ['goi y', 'tu van', 'tim giup', 'chon giup', 'nen mua', 'phu hop voi']);
    }

    private function nextMissingRecommendationDetail(string $message): ?string
    {
        if ($this->maximumBudget($message) === null) {
            return 'Bạn dự kiến ngân sách khoảng bao nhiêu để mình lọc đúng tầm giá?';
        }

        if (! $this->hasAny($message, ['phong khach', 'phong ngu', 'phong an', 'phong bep', 'm2', 'met vuong', 'kich thuoc'])) {
            return 'Bạn định đặt sản phẩm ở phòng nào, và không gian khoảng bao nhiêu m²?';
        }

        if (! $this->hasAny($message, ['go soi', 'go oc cho', 'go tu nhien', 'da', 'vai', 'kim loai', 'may tre', 'japandi', 'scandinavian', 'bac au', 'hien dai', 'toi gian'])) {
            return 'Bạn thích chất liệu hoặc phong cách nào để mình chọn sát hơn?';
        }

        return null;
    }

    private function findProducts(string $queryText): Collection
    {
        $query = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereHas('category', fn ($category) => $category->where('is_active', true))
            ->with(['category', 'primaryImage'])
            ->orderByDesc('stock');

        $category = $this->categoryFor($queryText);
        if ($category !== null) {
            $query->where(function ($builder) use ($category): void {
                foreach (self::CATEGORIES[$category] as $term) {
                    $builder->orWhere('name', 'like', '%' . $term . '%')
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $term . '%'));
                }
            });
        }

        $budget = $this->maximumBudget($queryText);
        if ($budget !== null) {
            $query->whereRaw('CASE WHEN discount_percent > 0 THEN ROUND(price * (1 - discount_percent / 100), -3) ELSE price END <= ?', [$budget]);
        }

        $products = $query->limit(100)->get();
        $material = $this->materialFor($queryText);
        if ($material !== null) {
            $products = $products->filter(fn (Product $product) => $this->matchesMaterial($this->normalize($product->material ?? ''), $material));
        }

        $color = $this->colorFor($queryText);
        if ($color !== null) {
            $products = $products->filter(fn (Product $product) => str_contains($this->normalize($product->color ?? ''), $color));
        }

        return $products->take(5)->values();
    }

    private function findAlternatives(string $queryText): Collection
    {
        $category = $this->categoryFor($queryText);
        $query = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->with(['category', 'primaryImage'])
            ->orderByDesc('stock');

        if ($category !== null) {
            $query->where(function ($builder) use ($category): void {
                foreach (self::CATEGORIES[$category] as $term) {
                    $builder->orWhere('name', 'like', '%' . $term . '%')
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $term . '%'));
                }
            });
        }

        $alternatives = $query->limit(5)->get();
        if ($alternatives->isEmpty() && $category !== null) {
            return Product::query()
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
                ->with(['category', 'primaryImage'])
                ->orderByDesc('stock')
                ->limit(5)
                ->get();
        }

        return $alternatives;
    }

    private function productCards(Collection $products): array
    {
        return $products->map(function (Product $product): array {
            $imageUrl = null;
            if ($product->primaryImage?->path) {
                $storedPath = ltrim($product->primaryImage->path, '/');
                $storedPath = preg_replace('#^(storage/)+#', '', $storedPath);
                $imageUrl = '/storage/' . $storedPath;
            }

            $attributes = array_filter([
                'material' => $product->material,
                'dimensions' => $product->dimensions,
                'color' => $product->color,
                'stock' => (int) $product->stock,
                'warranty' => $product->warranty_period,
            ], fn ($value) => $value !== null && $value !== '');

            return [
                'id' => (int) $product->id,
                'name' => $product->name,
                'imageUrl' => $imageUrl,
                'price' => (float) $product->final_price,
                'compareAtPrice' => $product->discount_percent > 0 ? (float) $product->price : null,
                'slug' => $product->slug,
                'url' => route('product.detail', ['slug' => $product->slug], false),
                'attributes' => $attributes,
            ];
        })->all();
    }

    private function categoryFor(string $message): ?string
    {
        if (in_array($message, ['tu', 'ghe', 'den'], true)) {
            return $message;
        }

        foreach (self::CATEGORIES as $category => $terms) {
            if ($this->hasAny($message, $terms)) {
                return $category;
            }
        }

        return null;
    }

    private function materialFor(string $message): ?string
    {
        foreach (self::MATERIALS as $material => $terms) {
            if ($this->hasAny($message, $terms)) {
                return $material;
            }
        }

        if ($this->hasAny($message, ['sofa da', 'ghe da', 'giuong da', 'chat lieu da'])) {
            return 'da';
        }

        return null;
    }

    private function colorFor(string $message): ?string
    {
        foreach (self::COLORS as $color) {
            if ($this->hasAny($message, ['mau ' . $color])) {
                return $color;
            }

            if (! in_array($color, ['do', 'den'], true) && $this->hasAny($message, [$color])) {
                return $color;
            }
        }

        return null;
    }

    private function matchesMaterial(string $productMaterial, string $requestedMaterial): bool
    {
        if ($requestedMaterial === 'go tu nhien') {
            return str_contains($productMaterial, 'go') && ! str_contains($productMaterial, 'cong nghiep');
        }

        if ($requestedMaterial === 'da') {
            return str_contains($productMaterial, 'da');
        }

        if ($requestedMaterial === 'kim loai') {
            return str_contains($productMaterial, 'kim loai') || str_contains($productMaterial, 'thep');
        }

        if ($requestedMaterial === 'may tre') {
            return str_contains($productMaterial, 'may') || str_contains($productMaterial, 'tre');
        }

        return str_contains($productMaterial, $requestedMaterial);
    }

    private function maximumBudget(string $message): ?int
    {
        if (! preg_match('/(?:duoi|toi da|ngan sach)\s*(\d+(?:[.,]\d+)?)\s*(trieu|tr|m|nghin|ngan|k)/', $message, $matches)
            && ! preg_match('/(\d+(?:[.,]\d+)?)\s*(trieu|tr|m|nghin|ngan|k)/', $message, $matches)) {
            return null;
        }

        $amount = (float) str_replace(',', '.', $matches[1]);
        $unit = $matches[2];
        $factor = in_array($unit, ['trieu', 'tr', 'm'], true) ? 1_000_000 : 1_000;

        return (int) round($amount * $factor);
    }

    private function hasAny(string $message, array $terms): bool
    {
        foreach ($terms as $term) {
            $pattern = '/(?<![a-z0-9])' . preg_quote($term, '/') . '(?![a-z0-9])/u';
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::ascii(mb_strtolower($value))));
    }
}
