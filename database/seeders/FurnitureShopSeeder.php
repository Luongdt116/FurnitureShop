<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FurnitureShopSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@auraliving.vn'],
            [
                'name' => 'Trần Minh Hoàng',
                'password' => Hash::make('password'),
                'phone' => '0908123456',
                'role' => 'admin',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
                'is_active' => true,
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@auraliving.vn'],
            [
                'name' => 'Nguyễn Thanh Tùng',
                'password' => Hash::make('password'),
                'phone' => '0912345678',
                'role' => 'customer',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
                'is_active' => true,
            ]
        );

        // 2. Seed Categories
        $categoriesData = [
            [
                'name' => 'Nội Thất Phòng Khách',
                'slug' => 'phong-khach',
                'image' => 'fa-couch',
                'description' => 'Sofa da cao cấp, bàn trà mặt đá, kệ tivi Bắc Âu',
                'sort_order' => 1,
            ],
            [
                'name' => 'Nội Thất Phòng Ngủ',
                'slug' => 'phong-ngu',
                'image' => 'fa-bed',
                'description' => 'Giường gỗ sồi, tủ quần áo cánh kính, bàn trang điểm',
                'sort_order' => 2,
            ],
            [
                'name' => 'Phòng Ăn & Nhà Bếp',
                'slug' => 'phong-an',
                'image' => 'fa-utensils',
                'description' => 'Bàn ăn 6-8 ghế gỗ óc chó, tủ bếp hiện đại',
                'sort_order' => 3,
            ],
            [
                'name' => 'Bàn Ghế Làm Việc',
                'slug' => 'lam-viec',
                'image' => 'fa-briefcase',
                'description' => 'Bàn công thái học, ghế lãnh đạo da bò thật',
                'sort_order' => 4,
            ],
            [
                'name' => 'Đèn & Trang Trí Decor',
                'slug' => 'trang-tri',
                'image' => 'fa-lightbulb',
                'description' => 'Đèn thả trần nghệ thuật mạ PVD, gương decor, thảm dệt',
                'sort_order' => 5,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. Seed Realistic Products
        $productsData = [
            [
                'sku' => 'SF-BG-01',
                'name' => 'Sofa Da Bò Ý Bergamo 3 Chỗ Cao Cấp',
                'slug' => 'sofa-da-bo-y-bergamo-3-cho',
                'category_slug' => 'phong-khach',
                'price' => 24500000,
                'discount_percent' => 15,
                'stock' => 12,
                'dimensions' => '220 x 95 x 85 cm',
                'material' => 'Khung gỗ thông Nga, Da bò Ý Mastrotto tự nhiên',
                'color' => 'Nâu Cognac, Đen Espresso, Kem Ivory',
                'origin' => 'Nhập khẩu nguyên chiếc Italy',
                'warranty_period' => '5 năm khung & da',
                'description' => 'Sofa Bergamo sở hữu đường nét tối giản tinh tế theo phong cách Nordic Luxury. Da bò thật mềm mại, đệm mút D40 chống lún xẹp tuyệt đối kết hợp lông vũ tạo cảm giác êm ái vượt trội.',
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'BA-ND-02',
                'name' => 'Bộ Bàn Ăn Gỗ Sồi Bắc Mỹ Nordic Luxe (Kèm 6 Ghế)',
                'slug' => 'bo-ban-an-go-soi-bac-my-nordic-luxe',
                'category_slug' => 'phong-an',
                'price' => 18900000,
                'discount_percent' => 14,
                'stock' => 8,
                'dimensions' => 'Bàn: 180 x 85 x 75 cm | Ghế: 48 x 52 x 82 cm',
                'material' => '100% Gỗ sồi trắng Bắc Mỹ (White Oak), Ghế nệm nỉ Bỉ',
                'color' => 'Gỗ tự nhiên (Natural), Nâu hạt dẻ (Walnut)',
                'origin' => 'Gia công xuất khẩu Bắc Âu',
                'warranty_period' => '3 năm chính hãng',
                'description' => 'Bộ bàn ăn Nordic Luxe được bo tròn cạnh an toàn cho gia đình có trẻ nhỏ. Bề mặt gỗ sồi sơn lót UV 5 lớp chống trầy xước, chịu nhiệt độ nước sôi tối ưu.',
                'images' => [
                    'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'GN-SC-03',
                'name' => 'Giường Ngủ Bọc Nỉ Phong Cách Scandinavian King Size',
                'slug' => 'giuong-ngu-boc-ni-scandinavian-king-size',
                'category_slug' => 'phong-ngu',
                'price' => 16500000,
                'discount_percent' => 15,
                'stock' => 15,
                'dimensions' => '180 x 200 x 110 cm (Lòng đệm 1m8)',
                'material' => 'Khung gỗ bạch dương, Vải nỉ nhung Bỉ kháng khuẩn',
                'color' => 'Xám Khói, Xanh Rêu Pastel, Kem Linen',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '5 năm khung giường',
                'description' => 'Thiết kế đầu giường gợn sóng tinh tế tạo điểm tựa lưng êm ái khi đọc sách. Khung nan cong đàn hồi thông minh trợ lực tối đa cho nệm lò xo túi cao cấp.',
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1540518614846-7ede433c4ef0?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'BLV-WL-04',
                'name' => 'Bàn Làm Việc Giám Đốc Gỗ Óc Chó Walnut Elegance',
                'slug' => 'ban-lam-viec-giam-doc-go-oc-cho-walnut',
                'category_slug' => 'lam-viec',
                'price' => 14200000,
                'discount_percent' => 15,
                'stock' => 6,
                'dimensions' => '160 x 75 x 75 cm',
                'material' => 'Gỗ óc chó Bắc Mỹ FAS 100%, lót da vi sợi',
                'color' => 'Nâu Trầm Óc Chó Tự Nhiên',
                'origin' => 'Xưởng mộc thủ công AURA',
                'warranty_period' => '5 năm toàn phần',
                'description' => 'Đỉnh cao phong cách làm việc của doanh nhân thành đạt. Thiết kế giấu dây điện thông minh, tích hợp sạc không dây 15W âm mặt bàn và ngăn kéo khóa mã số.',
                'images' => [
                    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'GH-EM-05',
                'name' => 'Ghế Thư Giãn Đọc Sách Eames Lounge Chair Kèm Đôn',
                'slug' => 'ghe-thu-gian-eames-lounge-chair',
                'category_slug' => 'phong-khach',
                'price' => 15900000,
                'discount_percent' => 14,
                'stock' => 10,
                'dimensions' => 'Ghế: 84 x 85 x 84 cm | Đôn: 65 x 54 x 44 cm',
                'material' => 'Vỏ gỗ ép uốn nhiệt 7 lớp veneer óc chó, Da bò Ý Semi-Aniline',
                'color' => 'Đen Cổ Điển, Nâu Amber, Trắng Sữa',
                'origin' => 'Nhập khẩu Hong Kong chuẩn 1:1',
                'warranty_period' => '3 năm',
                'description' => 'Biểu tượng kinh điển của thiết kế thế kỷ 20. Chân xoay hợp kim nhôm đúc chịu tải trọng 200kg, ngả lưng chuẩn 15 độ nâng niu cột sống tuyệt đối.',
                'images' => [
                    'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1580481077195-c22ae24269d5?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'DT-GL-06',
                'name' => 'Đèn Thả Bàn Ăn Thủy Tinh Nghệ Thuật Brass & Glass',
                'slug' => 'den-tha-ban-an-thuy-tinh-brass-glass',
                'category_slug' => 'trang-tri',
                'price' => 3600000,
                'discount_percent' => 20,
                'stock' => 25,
                'dimensions' => 'Đường kính 120 cm, dây treo tùy chỉnh 30 - 120 cm',
                'material' => 'Thân kim loại đồng thau mạ vàng PVD, Thủy tinh khói thổi thủ công',
                'color' => 'Đồng Mạ Vàng, Đen Nhám',
                'origin' => 'Hàn Quốc',
                'warranty_period' => '2 năm nguồn và chao',
                'description' => 'Tạo điểm nhấn lung linh cho phòng ăn sang trọng với 6 bóng LED tiết kiệm điện CRI > 90 phản chiếu ánh sáng chân thực ấm cúng cho bữa cơm sum vầy.',
                'images' => [
                    'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'TQ-CK-07',
                'name' => 'Tủ Quần Áo Cánh Kính Cường Lực Khung Nhôm Anode LED',
                'slug' => 'tu-quan-ao-canh-kinh-cuong-luc-khung-nhom',
                'category_slug' => 'phong-ngu',
                'price' => 28500000,
                'discount_percent' => 13,
                'stock' => 5,
                'dimensions' => '240 x 60 x 260 cm (4 cánh)',
                'material' => 'Thùng MDF lõi xanh chống ẩm An Cường, Kính xám khói cường lực 5mm',
                'color' => 'Thùng Gỗ Xám Titan, Thùng Gỗ Óc Chó',
                'origin' => 'Thiết kế thi công tại xưởng AURA',
                'warranty_period' => '5 năm phụ kiện & kính',
                'description' => 'Biến phòng ngủ thành showroom thời trang triệu đô với hệ cảm biến chạm tay mở đèn LED ray tự động thông minh và ngăn kéo phụ kiện chia trang sức bọc nhung.',
                'images' => [
                    'https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1558997519-83ea9252edf8?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'sku' => 'BT-MD-08',
                'name' => 'Bàn Trà Mặt Đá Ceramic Ý Chống Trầy Khung Mạ PVD',
                'slug' => 'ban-tra-mat-da-ceramic-y-chong-tray',
                'category_slug' => 'phong-khach',
                'price' => 7800000,
                'discount_percent' => 15,
                'stock' => 18,
                'dimensions' => 'Bộ đôi lồng ghép: Bàn lớn D80 x H45cm, Bàn nhỏ D60 x H40cm',
                'material' => 'Khung thép carbon mạ PVD vàng, Đá Ceramic chống nhiệt 1000°C',
                'color' => 'Trắng Vân Mây (Calacatta), Đen Tia Chớp (Marquina)',
                'origin' => 'Việt Nam xuất khẩu',
                'warranty_period' => '2 năm',
                'description' => 'Bàn trà đôi có thể kéo linh hoạt ra vào tiết kiệm không gian. Mặt đá phiến chống thấm ố trà cà phê, dễ dàng lau chùi chỉ với khăn ẩm.',
                'images' => [
                    'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        foreach ($productsData as $prod) {
            $catSlug = $prod['category_slug'];
            $category = $categories[$catSlug] ?? Category::first();

            $images = $prod['images'];
            unset($prod['images'], $prod['category_slug']);
            $prod['category_id'] = $category->id;
            $prod['is_active'] = true;

            $product = Product::firstOrCreate(['sku' => $prod['sku']], $prod);

            // Add images
            foreach ($images as $index => $imgPath) {
                ProductImage::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'path' => $imgPath,
                    ],
                    [
                        'alt_text' => $product->name . ' - góc chụp ' . ($index + 1),
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]
                );
            }
        }

        // 4. Seed Vouchers
        Coupon::firstOrCreate(
            ['code' => 'AURANEW'],
            [
                'description' => 'Giảm 10% tối đa 1.500.000₫ cho thành viên mới với đơn từ 5 triệu',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_discount' => 1500000,
                'min_order_amount' => 5000000,
                'starts_at' => now()->startOfYear(),
                'ends_at' => now()->endOfYear(),
                'usage_limit' => 200,
                'used_count' => 45,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'LUXE500'],
            [
                'description' => 'Giảm ngay 500.000₫ cho đơn hàng nội thất từ 8.000.000₫',
                'discount_type' => 'fixed',
                'discount_value' => 500000,
                'max_discount' => 500000,
                'min_order_amount' => 8000000,
                'starts_at' => now()->startOfYear(),
                'ends_at' => now()->endOfYear(),
                'usage_limit' => 100,
                'used_count' => 28,
                'is_active' => true,
            ]
        );
    }
}
