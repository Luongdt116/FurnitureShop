# TÀI LIỆU HƯỚNG DẪN TÍCH HỢP & QUY CHUẨN DỰ ÁN (INTEGRATION GUIDELINES)
> **Dự Án**: AURA LIVING &bull; Cửa Hàng Nội Thất Bắc Âu Cao Cấp (FurnitureShop)  
> **Nền tảng**: Laravel 13.x | PHP 8.3+ | Bootstrap 5.3.8 | Font Awesome 6.4.0 | Vite  
> **Tài liệu dành cho**: Teammate 2 (Quản lý Sản phẩm & Danh mục) & Teammate 3 (Đơn hàng, Khuyến mãi & Khách hàng)

---

## 1. PHÂN CHIA MODULE & PHẠM VI TRÁCH NHIỆM

Hệ thống được module hóa thành 3 phân hệ độc lập nhằm tránh xung đột code (conflict-free):

| Phân hệ | Thành viên phụ trách | Phạm vi nghiệp vụ | Các file & thư mục chính |
| :--- | :--- | :--- | :--- |
| **Module 1 (Platform & Reporting)** | **Chủ sở hữu nền tảng** | - Shared Layouts (`storefront`, `admin`), CSS Tokens, Components dùng chung.<br>- Storefront: Trang chủ (`home`), Danh mục (`catalog`), Chi tiết sản phẩm (`product-detail`).<br>- Admin: Bảng điều khiển (`dashboard`), Báo cáo doanh thu kế toán thuần túy (`reports/revenue`), Xuất Excel CSV, In ấn bản in.<br>- Cấu trúc định tuyến `routes/web.php` và seeder tổng hợp. | `app/Http/Controllers/StorefrontController.php`<br>`app/Http/Controllers/Admin/DashboardController.php`<br>`app/Http/Controllers/Admin/ReportController.php`<br>`app/Services/RevenueCalculationService.php`<br>`resources/views/layouts/`<br>`resources/views/components/`<br>`resources/css/` |
| **Module 2 (Catalog & Inventory)** | **Teammate 2** | - Quản lý Danh mục (Categories CRUD, thứ tự hiển thị, phòng mẫu).<br>- Quản lý Sản phẩm (Products CRUD, thông số kỹ thuật, chất liệu gỗ, kích thước, tải ảnh, giá bán, tồn kho).<br>- Tích hợp bộ lọc nâng cao trên Storefront (lọc giá, lọc chất liệu, lọc phòng). | `app/Http/Controllers/Admin/ProductController.php`<br>`app/Http/Controllers/Admin/CategoryController.php`<br>`resources/views/admin/products/`<br>`resources/views/admin/categories/` |
| **Module 3 (Orders & Transactions)** | **Teammate 3** | - Quản lý Đơn hàng (Orders CRUD, cập nhật tiến độ vận đơn: Chuẩn bị $\rightarrow$ Vận chuyển $\rightarrow$ Đã giao).<br>- Xử lý Đổi trả / Hoàn tiền (Returns & Refunds workflow: duyệt/từ chối, ghi nhận số tiền hoàn).<br>- Mã giảm giá (Vouchers/Coupons: tạo mã, hạn mức, % giảm, áp dụng giỏ hàng).<br>- Quản lý Thành viên (Users/Customers: tài khoản VIP, tích điểm, chặn tài khoản).<br>- Đánh giá sản phẩm (Product Reviews & Ratings). | `app/Http/Controllers/Admin/OrderController.php`<br>`app/Http/Controllers/Admin/VoucherController.php`<br>`app/Http/Controllers/Admin/UserController.php`<br>`resources/views/admin/orders/`<br>`resources/views/admin/vouchers/`<br>`resources/views/admin/users/`<br>`resources/views/storefront/cart.blade.php`<br>`resources/views/storefront/checkout.blade.php`<br>`resources/views/storefront/account.blade.php` |

---

## 2. QUY CHUẨN THIẾT KẾ (AURA LIVING DESIGN TOKENS)

Tất cả giao diện của Teammate 2 và Teammate 3 **bắt buộc tuân thủ hệ thống biến màu và font chữ dùng chung** trong `resources/css/tokens.css` để giữ sự nhất quán:

### 2.1 Bảng màu chủ đạo (Color Palette)
```css
/* Sử dụng trực tiếp biến CSS hoặc class tiện ích */
var(--primary)        /* #181513 - Đen than Bắc Âu (Tiêu đề, nút chính, header) */
var(--accent)         /* #c89b53 - Vàng ánh kim cao cấp (Điểm nhấn, hover, badge active) */
var(--accent-hover)   /* #b58842 - Vàng đậm khi hover */
var(--bg-subtle)      /* #f9f8f6 - Màu nền kem xám tinh tế cho khung thẻ, toolbar */
var(--border-color)   /* #e8e4de - Đường kẻ viền phân cách */
var(--text-primary)   /* #2c2724 - Màu chữ chính */
var(--text-muted)     /* #78716c - Màu chữ phụ, chú thích */
```

### 2.2 Trạng thái & Huy hiệu (Status Badges)
Khi hiển thị trạng thái đơn hàng, thanh toán hoặc đổi trả, hãy sử dụng các class có sẵn:
- `.badge.badge-success`: Đã giao hàng / Đã thanh toán / Hoàn thành / Còn hàng (`var(--status-success)`)
- `.badge.badge-warning`: Đang chuẩn bị / Chờ thanh toán / Sắp hết hàng (`var(--status-warning)`)
- `.badge.badge-danger`: Đã huỷ / Hoàn tiền / Hết hàng (`var(--status-danger)`)
- `.badge.badge-info`: Đang vận chuyển / Giao hàng (`var(--status-info)`)
- `.badge.badge-gold`: Chuẩn kế toán / Khách hàng VIP (`var(--accent)`)

### 2.3 Kiểu chữ (Typography)
- **Tiêu đề lớn, tên sản phẩm**: Sử dụng font có chân sang trọng:
  ```html
  <h2 class="font-heading">Bàn Ăn Gỗ Sồi Bắc Âu</h2>
  ```
- **Nội dung, bảng số liệu, form**: Font không chân hiện đại (Plus Jakarta Sans).

---

## 3. HƯỚNG DẪN KẾ THỪA MASTER LAYOUTS

### 3.1 Kế thừa Layout Quản Trị Admin (`layouts.admin`)
Mọi trang quản trị của Teammate 2 và 3 chỉ cần tạo view kế thừa `layouts.admin`:

```blade
@extends('layouts.admin')

@section('title', 'Quản Lý Sản Phẩm')

@push('styles')
    {{-- CSS riêng cho trang nếu có --}}
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 font-heading mb-1 text-primary">Danh Sách Sản Phẩm Nội Thất</h2>
        <span class="text-muted small">Quản lý danh mục, giá niêm yết và lượng tồn kho thực tế</span>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-accent btn-sm">
        <i class="fas fa-plus me-1"></i> Thêm sản phẩm mới
    </a>
</div>

{{-- Bảng dữ liệu chuẩn --}}
<div class="admin-card">
    <div class="table-toolbar">
        <div class="table-search-box">
            <input type="text" class="table-search-input" placeholder="Tìm theo tên, mã SKU...">
            <i class="fas fa-search table-search-icon"></i>
        </div>
        <div class="table-filter-actions">
            {{-- Bộ lọc bổ sung --}}
        </div>
    </div>
    
    <div class="data-table-wrap">
        <table class="data-table" id="admin-products-table">
            <thead>
                <tr>
                    <th style="min-width: 80px;">Hình Ảnh</th>
                    <th style="min-width: 200px;">Tên Sản Phẩm</th>
                    <th style="min-width: 120px;">Mã SKU</th>
                    <th style="min-width: 140px;">Danh Mục</th>
                    <th style="min-width: 120px;" class="text-end">Giá Niêm Yết</th>
                    <th style="min-width: 100px;" class="text-center">Kho Hàng</th>
                    <th style="min-width: 120px;" class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                {{-- Vòng lặp foreach ở đây --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    {{-- JavaScript riêng nếu có --}}
@endpush
```

### 3.2 Kế thừa Layout Khách Hàng Storefront (`layouts.storefront`)
Đối với các trang Cart, Checkout, Tài khoản cá nhân của Teammate 3:

```blade
@extends('layouts.storefront')

@section('title', 'Giỏ Hàng Của Bạn')

@section('content')
<div class="py-5 bg-white">
    <div class="container">
        {{-- Breadcrumb điều hướng --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
            </ol>
        </nav>

        {{-- Nội dung trang --}}
    </div>
</div>
@endsection
```

---

## 4. BỘ COMPONENT DÙNG CHUNG (REUSABLE UI COMPONENTS)

Hệ thống đã xây dựng sẵn các component Blade chuẩn hóa, các bạn chỉ cần gọi trực tiếp:

### 4.1 Thẻ Sản Phẩm (`<x-product-card>`)
Hiển thị thẻ sản phẩm chuẩn Aura Living, tự động định dạng tiền tệ VNĐ, nhãn giảm giá %, sao đánh giá, ảnh chính và nút thêm giỏ hàng Ajax:

```blade
<div class="row g-4">
    @foreach($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <x-product-card :product="$product" />
        </div>
    @endforeach
</div>
```

### 4.2 Thanh Phân Trang Chuẩn (`<x-pagination>`)
Hiển thị dòng đếm tổng sản phẩm và cụm nút trang bo tròn tinh tế:

```blade
{{-- Truyền biến paginator của Laravel vào --}}
<x-pagination :paginator="$products" />
```

### 4.3 Thông Báo Flash & Toast (`<x-flash-message>`, `<x-toast>`)
Layout đã tích hợp sẵn cả 2 component này. Trong Controller, bạn chỉ cần redirect kèm thông điệp:

```php
// Thông báo thành công (Hiển thị hộp xanh alert và toast góc phải)
return redirect()->route('admin.products.index')
    ->with('success', 'Đã thêm mới sản phẩm thành công!');

// Thông báo lỗi
return back()->with('error', 'Không thể xóa sản phẩm đang có đơn hàng liên kết!');

// Thông báo lưu ý
return back()->with('warning', 'Số lượng tồn kho sản phẩm này sắp hết!');
```

### 4.4 Hộp Thoại Modal (`<x-modal>`)
Tạo modal popup không cần code lại CSS:

```blade
<x-modal id="modal-quick-edit" title="Cập Nhật Nhanh Tồn Kho" size="md">
    <form action="..." method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Số lượng trong kho:</label>
            <input type="number" name="stock" class="form-control" value="10">
        </div>
        <button type="submit" class="btn btn-accent btn-sm">Lưu thay đổi</button>
    </form>
</x-modal>

{{-- Để mở/đóng modal từ JavaScript hoặc nút bấm: --}}
<button onclick="openAuraModal('modal-quick-edit')" class="btn btn-primary btn-sm">Mở</button>
<button onclick="closeAuraModal('modal-quick-edit')" class="btn btn-outline btn-sm">Đóng</button>
```

---

## 5. QUY TẮC NGHIỆP VỤ KẾ TOÁN THUẦN TÚY (CRITICAL BUSINESS RULES)

> [!IMPORTANT]
> **Đặc biệt lưu ý cho Teammate 3 (Xử lý đơn hàng, hoàn trả và doanh thu)**:
> Bảng điều khiển và Báo cáo tài chính đã được kiểm định nghiêm ngặt theo chuẩn kế toán thuần túy. Mọi xử lý liên quan đến trạng thái đơn hàng bắt buộc phải tuân theo quy tắc sau:

1. **Điều kiện ghi nhận Doanh thu thuần**:
   - **Chỉ tính doanh thu** khi đơn hàng thỏa mãn đồng thời 2 điều kiện:
     `status === 'delivered'` VÀ `payment_status === 'paid'`.
   - Các đơn hàng đang ở trạng thái `pending`, `preparing`, `shipping`, `awaiting_shipment` **chưa được ghi nhận vào doanh thu kế toán** (chỉ tính là doanh thu dự kiến).
2. **Quy tắc Đơn huỷ (`cancelled`)**:
   - Mọi đơn hàng có `status === 'cancelled'` hoặc `payment_status === 'cancelled'` $\rightarrow$ Doanh thu kế toán ghi nhận chính xác là **0₫**.
3. **Quy tắc Đổi trả & Hoàn tiền (Returns vs Refunds)**:
   - **Hoàn tiền (`return_type === 'refund'` và `status === 'completed'`)**:
     Bắt buộc trừ đúng giá trị `refund_amount` khỏi doanh thu gộp của đơn hàng đó.
     $\text{Doanh thu thuần đơn hàng} = \max(0, \text{Tổng thanh toán} - \text{Tiền hoàn trả})$.
   - **Đổi hàng (`return_type === 'exchange'`)**:
     Nếu chỉ đổi sản phẩm cùng giá trị hoặc khác màu/kích thước mà không phát sinh hoàn tiền cho khách $\rightarrow$ **Giữ nguyên 100% doanh thu ban đầu**, không bị trừ doanh thu.
4. **Tái sử dụng Service tính toán**:
   - Khi cần trích xuất số liệu doanh thu hoặc đối soát tài chính, hãy gọi trực tiếp service:
     `app/Services/RevenueCalculationService.php`.
   - Tuyệt đối không tự viết lại câu lệnh tính doanh thu rời rạc để tránh trường hợp số liệu giữa Dashboard và Báo cáo bị lệch nhau.

---

## 6. DANH MỤC ROUTES ĐÃ ĐĂNG KÝ SẴN & CÁCH CẮM CONTROLLER

Trong file `routes/web.php`, tất cả các route cần thiết đã được đăng ký sẵn với tên route chuẩn và view placeholder an toàn (không bao giờ bị 404). Các bạn chỉ việc tạo Controller tương ứng và thay thế dòng route:

### 6.1 Dành cho Teammate 2 (Sản phẩm & Danh mục)
| Route Name | Phương thức & URL | Controller Action cần cắm | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `admin.products.index` | `GET /admin/products` | `ProductController@index` | Danh sách sản phẩm quản trị |
| `admin.products.create` | `GET /admin/products/create` | `ProductController@create` | Form thêm mới sản phẩm |
| `admin.products.store` | `POST /admin/products` | `ProductController@store` | Lưu sản phẩm mới vào DB |
| `admin.products.edit` | `GET /admin/products/{id}/edit` | `ProductController@edit` | Form chỉnh sửa sản phẩm |
| `admin.products.update` | `PUT /admin/products/{id}` | `ProductController@update` | Cập nhật thông tin sản phẩm |
| `admin.products.destroy` | `DELETE /admin/products/{id}` | `ProductController@destroy` | Xóa mềm sản phẩm |
| `admin.categories.index` | `GET /admin/categories` | `CategoryController@index` | Quản lý danh mục nội thất |

### 6.2 Dành cho Teammate 3 (Đơn hàng, Khuyến mãi & Khách hàng)
| Route Name | Phương thức & URL | Controller Action cần cắm | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `admin.orders.index` | `GET /admin/orders` | `OrderController@index` | Danh sách đơn hàng toàn hệ thống |
| `admin.orders.show` | `GET /admin/orders/{id}` | `OrderController@show` | Chi tiết đơn hàng & tiến trình vận đơn |
| `admin.orders.status` | `POST /admin/orders/{id}/status` | `OrderController@updateStatus` | Cập nhật trạng thái đơn hàng |
| `admin.vouchers.index` | `GET /admin/vouchers` | `VoucherController@index` | Danh sách & tạo mã giảm giá |
| `admin.users.index` | `GET /admin/users` | `UserController@index` | Quản lý thành viên & phân quyền |
| `cart.coupon.apply` | `POST /cart/coupon/apply` | `CartController@applyCoupon` | Áp dụng mã ưu đãi trong giỏ hàng |
| `orders.return.request`| `POST /orders/{id}/return-request`| `ReturnController@request` | Khách gửi yêu cầu đổi trả |
| `reviews.store` | `POST /product/{id}/reviews` | `ReviewController@store` | Đánh giá sao & nhận xét sản phẩm |

---

## 7. QUY TRÌNH PHỐI HỢP GIT (COLLABORATION WORKFLOW)

1. **Lấy code mới nhất từ nhánh `main`**:
   ```bash
   git checkout main
   git pull origin main --rebase
   ```
2. **Tạo nhánh làm việc riêng cho từng task**:
   ```bash
   # Ví dụ Teammate 2 làm Sản phẩm:
   git checkout -b feature/product-management

   # Ví dụ Teammate 3 làm Đơn hàng:
   git checkout -b feature/order-management
   ```
3. **Trước khi gộp vào `main`**:
   ```bash
   # Luôn rebase trên main mới nhất để kiểm tra conflict
   git fetch origin
   git rebase origin/main
   
   # Chạy test kiểm tra cú pháp
   php -l routes/web.php
   ```
4. **Biên dịch tài nguyên sau khi thêm CSS/JS**:
   ```bash
   npm run build
   ```

---
*AURA LIVING Development Team &bull; Bản quyền tài liệu thuộc về Nhóm dự án.*
