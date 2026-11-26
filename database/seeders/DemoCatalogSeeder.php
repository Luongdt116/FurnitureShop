<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'phong-khach' => Category::updateOrCreate(
                ['slug' => 'phong-khach'],
                ['name' => 'Nội Thất Phòng Khách', 'image' => 'fa-couch', 'description' => 'Sofa, bàn trà và nội thất phòng khách.', 'sort_order' => 1, 'is_active' => true]
            ),
            'phong-an' => Category::updateOrCreate(
                ['slug' => 'phong-an'],
                ['name' => 'Phòng Ăn & Nhà Bếp', 'image' => 'fa-utensils', 'description' => 'Bàn ăn và nội thất phòng ăn.', 'sort_order' => 2, 'is_active' => true]
            ),
            'phong-ngu' => Category::updateOrCreate(
                ['slug' => 'phong-ngu'],
                ['name' => 'Nội Thất Phòng Ngủ', 'image' => 'fa-bed', 'description' => 'Giường và nội thất phòng ngủ.', 'sort_order' => 3, 'is_active' => true]
            ),
        ];

        $products = [
            ['sku' => 'DEMO-SF-001', 'name' => 'Sofa Da Bò Ý Bergamo 3 Chỗ', 'slug' => 'demo-sofa-da-bo-y-bergamo', 'category' => 'phong-khach', 'price' => 24500000, 'discount_percent' => 15, 'stock' => 12, 'material' => 'Khung gỗ tự nhiên, da bò Ý', 'dimensions' => '220 x 95 x 85 cm', 'color' => 'Nâu Cognac', 'origin' => 'Italy', 'warranty_period' => '5 năm', 'description' => 'Sofa mẫu phục vụ kiểm tra catalog, giỏ hàng và tồn kho.'],
            ['sku' => 'DEMO-BT-002', 'name' => 'Bàn Trà Mặt Đá Ceramic', 'slug' => 'demo-ban-tra-mat-da-ceramic', 'category' => 'phong-khach', 'price' => 7800000, 'discount_percent' => 10, 'stock' => 18, 'material' => 'Đá ceramic, khung thép', 'dimensions' => 'D80 x H45 cm', 'color' => 'Trắng vân mây', 'origin' => 'Việt Nam', 'warranty_period' => '2 năm', 'description' => 'Bàn trà mẫu phục vụ kiểm tra thêm sản phẩm cùng nhóm.'],
            ['sku' => 'DEMO-KTV-003', 'name' => 'Kệ Tivi Gỗ Sồi Bắc Âu', 'slug' => 'demo-ke-tivi-go-soi-bac-au', 'category' => 'phong-khach', 'price' => 9200000, 'discount_percent' => 0, 'stock' => 9, 'material' => 'Gỗ sồi tự nhiên', 'dimensions' => '180 x 40 x 55 cm', 'color' => 'Gỗ sồi tự nhiên', 'origin' => 'Việt Nam', 'warranty_period' => '3 năm', 'description' => 'Kệ tivi mẫu phục vụ kiểm tra cập nhật số lượng.'],
            ['sku' => 'DEMO-BA-004', 'name' => 'Bộ Bàn Ăn Gỗ Sồi 6 Ghế', 'slug' => 'demo-bo-ban-an-go-soi-6-ghe', 'category' => 'phong-an', 'price' => 18900000, 'discount_percent' => 8, 'stock' => 8, 'material' => 'Gỗ sồi tự nhiên', 'dimensions' => '180 x 90 x 75 cm', 'color' => 'Sồi sáng', 'origin' => 'Việt Nam', 'warranty_period' => '5 năm', 'description' => 'Bộ bàn ăn mẫu phục vụ kiểm tra giỏ hàng nhiều sản phẩm.'],
            ['sku' => 'DEMO-GN-005', 'name' => 'Giường Ngủ Gỗ Sồi Japandi', 'slug' => 'demo-giuong-ngu-go-soi-japandi', 'category' => 'phong-ngu', 'price' => 16500000, 'discount_percent' => 12, 'stock' => 6, 'material' => 'Gỗ sồi tự nhiên', 'dimensions' => '160 x 200 cm', 'color' => 'Nâu gỗ', 'origin' => 'Việt Nam', 'warranty_period' => '5 năm', 'description' => 'Giường ngủ mẫu phục vụ kiểm tra tồn kho giới hạn.'],
        ];

        foreach ($products as $data) {
            $category = $categories[$data['category']];
            unset($data['category']);
            Product::updateOrCreate(
                ['sku' => $data['sku']],
                [...$data, 'category_id' => $category->id, 'is_active' => true]
            );
        }
    }
}
