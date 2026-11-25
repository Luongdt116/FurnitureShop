<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

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

Route::get('/login', function () {
    return view('storefront.auth.login');
})->name('login.show');

Route::get('/register', function () {
    return view('storefront.auth.register');
})->name('register.show');

Route::post('/login', function (Request $request) {
    $guestSessionId = $request->session()->get('pending_guest_cart_session_id') ?? $request->session()->getId();
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials['is_active'] = true;

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        try {
            app(CartService::class)->mergeGuestCart(Auth::user(), $guestSessionId);
            $request->session()->forget('pending_guest_cart_session_id');
        } catch (ValidationException $exception) {
            $request->session()->put('pending_guest_cart_session_id', $guestSessionId);

            return redirect()->route('cart.index')
                ->withErrors($exception->errors())
                ->with('warning', 'Đã đăng nhập nhưng giỏ khách chưa thể gộp hoàn toàn. Các sản phẩm vẫn được giữ trong giỏ để bạn điều chỉnh.');
        }

        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Chào mừng Quản trị viên!');
        }
        $redirect = $request->input('redirect');
        if (! is_string($redirect) || ! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = route('account.profile');
        }

        return redirect()->intended($redirect)->with('success', 'Đăng nhập thành công!');
    }

    return back()->withErrors([
        'email' => 'Thông tin đăng nhập không chính xác hoặc tài khoản chưa kích hoạt.',
    ])->onlyInput('email');
})->name('login');

Route::post('/register', function (Request $request) {
    $guestSessionId = $request->session()->get('pending_guest_cart_session_id') ?? $request->session()->getId();
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
    $request->session()->regenerate();

    try {
        app(CartService::class)->mergeGuestCart($user, $guestSessionId);
        $request->session()->forget('pending_guest_cart_session_id');
    } catch (ValidationException $exception) {
        $request->session()->put('pending_guest_cart_session_id', $guestSessionId);

        return redirect()->route('cart.index')
            ->withErrors($exception->errors())
            ->with('warning', 'Tài khoản đã được tạo nhưng giỏ khách chưa thể gộp hoàn toàn. Các sản phẩm vẫn được giữ trong giỏ để bạn điều chỉnh.');
    }
    $redirect = $request->input('redirect');
    if (! is_string($redirect) || ! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
        $redirect = route('account.profile');
    }

    return redirect()->intended($redirect)->with('success', 'Đăng ký tài khoản thành viên thành công!');
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

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
Route::patch('/cart/products/{product}', [CartController::class, 'updateProduct'])->name('cart.product.update');
Route::patch('/cart/items/{cart}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

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
Route::get('/checkout', [CheckoutController::class, 'index'])->middleware('auth')->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'store'])->middleware('auth')->name('checkout.process');


// =========================================================================
// SECTION 4: CUSTOMER PORTAL, REVIEWS & RETURNS (Teammate 3)
// =========================================================================

Route::middleware('auth')->group(function () {
    Route::get('/account/profile', function () {
        return view('storefront.account', ['tab' => 'profile']);
    })->name('account.profile');

    Route::get('/account/addresses', function () {
        return view('storefront.account', ['tab' => 'addresses']);
    })->name('account.addresses');

    Route::get('/orders', function (Request $request) {
        $orders = $request->user()->orders()->with('items')->latest()->get();
        return view('storefront.account', ['tab' => 'orders', 'orders' => $orders]);
    })->name('orders.index');

    Route::post('/orders/{id}/return-request', function ($id, Request $request) {
        return back()->with('success', 'Yêu cầu đổi trả/hoàn tiền đã được gửi. Bộ phận CSKH sẽ liên hệ trong 24h.');
    })->name('orders.return.request');

    Route::post('/product/{id}/reviews', function ($id, Request $request) {
        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm! Đánh giá đang chờ kiểm duyệt.');
    })->name('reviews.store');
});

// Guest order lookup
Route::get('/orders-lookup', function () {
    return view('storefront.account', ['tab' => 'orders']);
})->name('orders.lookup');


// =========================================================================
// SECTION 5: ADMIN PORTAL & REPORTS (Module 1, Teammate 2 & Teammate 3)
// =========================================================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
    Route::get('/orders', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Đơn Hàng & Đổi Trả',
            'icon' => 'fas fa-receipt',
            'assignedTo' => 'Teammate 3 (Phụ trách Đơn hàng & Thanh toán)',
            'description' => 'Theo dõi luồng trạng thái đơn hàng (Chuẩn bị -> Vận chuyển -> Đã giao), xử lý khiếu nại và duyệt yêu cầu đổi trả / hoàn tiền.',
            'routeTarget' => 'App\Http\Controllers\Admin\OrderController@index',
        ]);
    })->name('orders.index');

    Route::get('/orders/{id}', function ($id) {
        return view('admin.placeholder', [
            'title' => 'Chi Tiết Đơn Hàng #' . $id,
            'icon' => 'fas fa-file-invoice',
            'assignedTo' => 'Teammate 3',
            'description' => 'Chi tiết sản phẩm, thông tin giao hàng, đối soát thanh toán và cập nhật trạng thái vận đơn.',
            'routeTarget' => 'App\Http\Controllers\Admin\OrderController@show',
        ]);
    })->name('orders.show');

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
    Route::get('/users', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Thành Viên & Khách Hàng',
            'icon' => 'fas fa-users',
            'assignedTo' => 'Teammate 3 (Phụ trách Tài khoản & Khách hàng)',
            'description' => 'Danh sách tài khoản khách hàng VIP/Guest, phân quyền quản trị viên và lịch sử mua sắm tích điểm.',
            'routeTarget' => 'App\Http\Controllers\Admin\UserController@index',
        ]);
    })->name('users.index');
});
