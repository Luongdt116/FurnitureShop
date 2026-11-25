# AURA LIVING &bull; Cửa Hàng Nội Thất Bắc Âu Cao Cấp
> Hệ thống thương mại điện tử chuyên cung cấp các giải pháp nội thất Bắc Âu (Scandinavian) và Japandi cao cấp.  
> Được phát triển trên nền tảng **Laravel 13.x**, **Bootstrap 5.3.8**, **Font Awesome 6.4.0** và **Vite**.

---

## 1. CÔNG NGHỆ & YÊU CẦU HỆ THỐNG
- **Ngôn ngữ**: PHP 8.3+
- **Framework**: Laravel 13.x
- **Cơ sở dữ liệu**: MySQL 8.0+ (hỗ trợ Docker Compose)
- **Frontend Framework**: Bootstrap 5.3.8, Font Awesome 6.4.0
- **Build tool**: Vite 6.x
- **Chuẩn thiết kế**: Aura Living Design System (`resources/css/tokens.css`)

---

## 2. HƯỚNG DẪN CÀI ĐẶT NHANH (QUICK START)

```bash
# 1. Cài đặt các thư viện PHP
composer install

# 2. Cài đặt và đóng gói tài nguyên frontend
npm install
npm run build

# 3. Tạo file cấu hình môi trường .env (nếu chưa có)
cp .env.example .env
php artisan key:generate

# 4. Khởi chạy Docker MySQL (nếu dùng Docker)
docker compose up -d

# 5. Chạy migration và nạp dữ liệu mẫu chuẩn kế toán
php artisan migrate:fresh --seed --seeder=FurnitureShopSeeder

# 6. Khởi động máy chủ phát triển
php artisan serve
```

Truy cập:
- **Giao diện Cửa hàng (Storefront)**: [http://localhost:8000](http://localhost:8000)
- **Bảng điều khiển Quản trị (Admin Dashboard)**: [http://localhost:8000/admin/dashboard](http://localhost:8000/admin/dashboard)
- **Báo cáo doanh thu chuẩn kế toán**: [http://localhost:8000/admin/reports/revenue](http://localhost:8000/admin/reports/revenue)

---

## 3. PHÂN CHIA NHIỆM VỤ NHÓM PHÁT TRIỂN

| Thành viên | Phân hệ phụ trách | Trạng thái |
| :--- | :--- | :--- |
| **Teammate 1 (Platform & Reporting)** | **Nền tảng giao diện, Storefront cốt lõi & Báo cáo kế toán**: <br>- Master Layouts (`layouts.storefront`, `layouts.admin`), CSS Tokens, Reusable Components.<br>- Trang chủ, Trang danh mục kèm lọc, Trang chi tiết sản phẩm, Giỏ hàng trượt Drawer.<br>- Bảng điều khiển quản trị (Dashboard KPIs, Biểu đồ SVG 6 tháng, Cảnh báo tồn kho).<br>- Báo cáo doanh thu kế toán thuần túy (Bộ lọc kỳ, Bảng đối soát 10 cột, Xuất Excel CSV, In ấn bản in A4).<br>- Khung điều phối định tuyến chung (`routes/web.php`) và Tài liệu tích hợp. | **Hoàn thành (Tasks 1 - 7)** |
| **Teammate 2** | **Quản trị Danh mục & Sản phẩm**: <br>- CRUD Danh mục sản phẩm, thứ tự hiển thị, phòng trưng bày.<br>- CRUD Sản phẩm, thông số kỹ thuật, chất liệu, kích thước, quản lý kho hàng.<br>- Tích hợp bộ lọc sâu danh mục Storefront. | *Đang triển khai* |
| **Teammate 3** | **Quản trị Đơn hàng & Dịch vụ khách hàng**: <br>- Luồng đặt hàng Checkout, Quản lý đơn hàng (Orders).<br>- Quy trình đổi trả / hoàn tiền (Returns & Refunds workflow).<br>- Quản lý Mã giảm giá (Vouchers) & Thành viên (Users).<br>- Đánh giá và xếp hạng sản phẩm (Reviews). | *Đang triển khai* |

---

## 4. TÀI LIỆU DÀNH CHO THÀNH VIÊN TRONG NHÓM

Tài liệu chi tiết hướng dẫn Teammate 2 và Teammate 3 cách tích hợp code, kế thừa layout, dùng components chung và cắm controller:
👉 **[Xem Tài Liệu Hướng Dẫn Tích Hợp Chi Tiết (INTEGRATION GUIDE)](docs/INTEGRATION_GUIDE.md)**

---

## 5. TÀI KHOẢN MẪU ĐỂ KIỂM THỬ (DEMO CREDENTIALS)
- **Quản trị viên (Admin)**: `admin@auraliving.vn` | Mật khẩu: `admin123`
- **Khách hàng mẫu (Customer)**: `customer@gmail.com` | Mật khẩu: `password123`
