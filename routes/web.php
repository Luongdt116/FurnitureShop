<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReturnRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - AURA LIVING (FurnitureShop)
|--------------------------------------------------------------------------
|
| Central routes registry for the AURA LIVING project.
| Standardized for 3 teammate modules:
| - Module 1 (Owner): Platform UI, Layouts, Storefront base, Admin Dashboard, Pure Accounting Reports.
| - Module 2 (Teammate 2): Catalog, Products, Categories, Attributes/Specs & Inventory Management.
| - Module 3 (Teammate 3): Orders, Checkout, Cart, Vouchers, Customer Accounts, Reviews & Returns.
|
*/

// =========================================================================
// SECTION 1: STOREFRONT PUBLIC & INFORMATIONAL ROUTES (Module 1 & 2)
// =========================================================================

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/catalog', [StorefrontController::class, 'catalog'])->name('catalog.index');
Route::get('/catalog/search', [StorefrontController::class, 'search'])->name('catalog.search');
Route::get('/catalog/suggestions', [StorefrontController::class, 'searchSuggestions'])->name('catalog.suggestions');
Route::get('/product/{slug}', [StorefrontController::class, 'productDetail'])->name('product.detail');

// Storefront Informational Pages (About, Contact, Policies)
Route::get('/about', function () {
    return view('storefront.page', [
        'title' => 'Câu Chuyện Thương Hiệu AURA LIVING',
        'subtitle' => 'Kiến tạo phong cách sống Bắc Âu vượt thời gian',
        'content' => 'AURA LIVING tự hào mang đến các bộ sưu tập nội thất tối giản, thanh lịch, kết hợp hoàn hảo giữa nét mộc mạc của gỗ sồi tự nhiên và công nghệ gia công chuẩn E1 châu Âu. Từng đường nét thiết kế đều được chăm chút tỉ mỉ nhằm tôn vinh sự ấm cúng trong mỗi không gian sống gia đình.',
    ]);
})->name('about');

Route::get('/contact', function () {
    return view('storefront.page', [
        'title' => 'Liên Hệ & Trải Nghiệm Showroom',
        'subtitle' => 'Đội ngũ tư vấn thiết kế nội thất luôn sẵn sàng lắng nghe bạn',
        'content' => 'Showroom AURA LIVING: 720A Điện Biên Phủ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh.<br>Hotline: 1800 6868 (Miễn phí cước) &bull; Email: contact@auraliving.vn &bull; Giờ mở cửa: 08:30 - 21:00 hàng ngày.',
    ]);
})->name('contact');

Route::get('/terms', function () {
    return view('storefront.page', [
        'title' => 'Điều Khoản Dịch Vụ & Sử Dụng',
        'subtitle' => 'Quy định bảo vệ quyền lợi khách hàng và giao dịch minh bạch',
        'content' => 'Khi mua sắm tại AURA LIVING, quý khách được đảm bảo quyền lợi đổi trả minh bạch trong 7 ngày, bảo hành kết cấu 5 năm cho các sản phẩm gỗ tự nhiên, và miễn phí vận chuyển tận căn hộ.',
    ]);
})->name('policy.terms');

Route::get('/return-policy', function () {
    return view('storefront.page', [
        'title' => 'Chính Sách Đổi Trả & Bảo Hành Minh Bạch',
        'subtitle' => 'Quy trình giải quyết nhanh chóng trong 7 ngày theo chuẩn kiểm định',
        'content' => 'Áp dụng cho mọi sản phẩm phát hiện lỗi kỹ thuật gia công hoặc hư hỏng trong quá trình vận chuyển. Đổi hàng mới giữ nguyên quyền lợi bảo hành hoặc hoàn tiền 100% qua phương thức thanh toán ban đầu.',
    ]);
})->name('policy.returns');

// Newsletter subscription
Route::post('/newsletter/subscribe', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    return back()->with('success', 'Cảm ơn bạn đã đăng ký nhận bản tin AURA LIVING!');
})->name('newsletter.subscribe');


// =========================================================================
// SECTION 2: AUTHENTICATION & ACCESS CONTROL (RBAC Ready)
// =========================================================================

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $credentials['is_active'] = true;

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        if (Auth::user()->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Chào mừng Quản trị viên!');
        }
        return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công!');
    }

    return back()->withErrors([
        'email' => 'Thông tin đăng nhập không chính xác hoặc tài khoản chưa kích hoạt.',
    ])->onlyInput('email');
})->name('login');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:150',
        'email' => 'required|email|unique:users,email',
        'phone' => ['required', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
        'password' => 'required|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'password' => $validated['password'],
        'role' => 'customer',
        'is_active' => true,
    ]);

    Auth::login($user);
    return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành viên thành công!');
})->name('register');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home')->with('success', 'Đã đăng xuất tài khoản an toàn.');
})->name('logout');


// =========================================================================
// SECTION 3: CART, CHECKOUT & TRANSACTIONS (Module 1 & Teammate 3)
// =========================================================================

Route::get('/cart', function () {
    return view('storefront.cart');
})->name('cart.index');

Route::post('/cart/add', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}', [StorefrontController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/clear', [StorefrontController::class, 'clearCart'])->name('cart.clear');

// Vouchers & Coupons in Cart
Route::post('/cart/coupon/apply', function (Request $request) {
    $code = strtoupper(trim($request->input('coupon_code', '')));
    if ($code === 'AURA10') {
        session()->put('applied_coupon', [
            'code' => 'AURA10',
            'discount_percent' => 10,
            'description' => 'Ưu đãi thành viên mới - Giảm 10%',
        ]);
        return back()->with('success', 'Áp dụng mã ưu đãi AURA10 thành công! Đã giảm 10%.');
    }
    return back()->with('error', 'Mã ưu đãi không hợp lệ hoặc đã hết lượt sử dụng.');
})->name('cart.coupon.apply');

Route::post('/cart/coupon/remove', function () {
    session()->forget('applied_coupon');
    return back()->with('info', 'Đã gỡ bỏ mã ưu đãi khỏi giỏ hàng.');
})->name('cart.coupon.remove');

// Checkout Process
Route::get('/checkout', function () {
    return view('storefront.checkout');
})->name('checkout.index');

Route::post('/checkout/process', function (Request $request) {
    return redirect()->route('home')->with('success', 'Đặt hàng thành công! Đơn hàng đang được chuẩn bị.');
})->name('checkout.process');


// =========================================================================
// SECTION 4: CUSTOMER PORTAL, REVIEWS & RETURNS (Teammate 3)
// =========================================================================

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/account/profile', function () {
        return view('storefront.account', ['tab' => 'profile']);
    })->name('account.profile');

    Route::get('/account/addresses', function () {
        return view('storefront.account', ['tab' => 'addresses']);
    })->name('account.addresses');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/returns', [ReturnRequestController::class, 'index'])->name('orders.returns.index');
    Route::post('/orders/{order}/returns', [ReturnRequestController::class, 'store'])->name('orders.returns.store');
    Route::get('/orders/{order}/returns/{returnRequest}', [ReturnRequestController::class, 'show'])->name('orders.returns.show');
    Route::post('/orders/{order}/items/{orderItem}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Guest order lookup
Route::get('/orders-lookup', function () {
    return view('storefront.account', ['tab' => 'orders']);
})->name('orders.lookup');


// =========================================================================
// SECTION 5: ADMIN PORTAL & REPORTS (Module 1, Teammate 2 & Teammate 3)
// =========================================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // 5.1 Admin Root Redirect
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // 5.2 Dashboard & Pure Accounting Reports (Module 1 - Done)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/revenue/export', [ReportController::class, 'exportRevenueCsv'])->name('reports.revenue.export');

    // 5.3 Products Management (Teammate 2 Slot)
    Route::get('/products', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Danh Mục & Sản Phẩm Nội Thất',
            'icon' => 'fas fa-boxes-stacked',
            'assignedTo' => 'Teammate 2 (Phụ trách Sản phẩm & Tồn kho)',
            'description' => 'Xem danh sách sản phẩm, thêm/sửa/xóa thông số kỹ thuật, quản lý giá bán, kho hàng và tải lên hình ảnh.',
            'routeTarget' => 'App\Http\Controllers\Admin\ProductController@index',
        ]);
    })->name('products.index');

    Route::get('/products/create', function () {
        return view('admin.placeholder', [
            'title' => 'Thêm Mới Sản Phẩm Nội Thất',
            'icon' => 'fas fa-plus',
            'assignedTo' => 'Teammate 2',
            'description' => 'Form tạo mới sản phẩm: tên, SKU, chất liệu, kích thước, ảnh đại diện, giá niêm yết và kho hàng.',
            'routeTarget' => 'App\Http\Controllers\Admin\ProductController@create',
        ]);
    })->name('products.create');

    // 5.4 Categories Management (Teammate 2 Slot)
    Route::get('/categories', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Danh Mục Nội Thất',
            'icon' => 'fas fa-tags',
            'assignedTo' => 'Teammate 2 (Phụ trách Danh mục)',
            'description' => 'Tổ chức phân loại danh mục sản phẩm, cấu hình thứ tự hiển thị và phòng trưng bày tương ứng.',
            'routeTarget' => 'App\Http\Controllers\Admin\CategoryController@index',
        ]);
    })->name('categories.index');

    // 5.5 Orders & Returns Management (Teammate 3 Slot)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.tracking');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/returns/{returnRequest}', [AdminOrderController::class, 'updateReturn'])->name('returns.update');

    // 5.6 Vouchers & Promotion Management (Teammate 3 Slot)
    Route::get('/vouchers', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Mã Giảm Giá & Voucher',
            'icon' => 'fas fa-ticket-simple',
            'assignedTo' => 'Teammate 3 (Phụ trách Khuyến mãi)',
            'description' => 'Thiết lập mã coupon giảm giá theo %, giảm tiền cố định, giới hạn lượt dùng và ngày hết hạn.',
            'routeTarget' => 'App\Http\Controllers\Admin\VoucherController@index',
        ]);
    })->name('vouchers.index');

    // 5.7 Users & Members Management (Teammate 3 Slot)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
});
