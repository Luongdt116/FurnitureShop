<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FurnitureShop (Aura Living Standard)
|--------------------------------------------------------------------------
|
| Central routes owned by Storefront & Reporting Module.
| Teammates 2 and 3 integrate by providing route name, action, and controller.
|
*/

// ==========================================
// 1. STOREFRONT PUBLIC ROUTES
// ==========================================
Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/catalog', [StorefrontController::class, 'catalog'])->name('catalog.index');
Route::get('/catalog/search', [StorefrontController::class, 'search'])->name('catalog.search');
Route::get('/catalog/suggestions', [StorefrontController::class, 'searchSuggestions'])->name('catalog.suggestions');
Route::get('/product/{slug}', [StorefrontController::class, 'productDetail'])->name('product.detail');

// Newsletter subscription
Route::post('/newsletter/subscribe', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    return back()->with('success', 'Cảm ơn bạn đã đăng ký nhận bản tin AURA LIVING!');
})->name('newsletter.subscribe');

// ==========================================
// 2. AUTHENTICATION (DEMO & RBAC READY)
// ==========================================
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

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

// ==========================================
// 3. CART & CHECKOUT (STOREFRONT)
// ==========================================
Route::get('/cart', function () {
    return view('storefront.cart');
})->name('cart.index');

Route::post('/cart/add', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}', [StorefrontController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/clear', [StorefrontController::class, 'clearCart'])->name('cart.clear');

Route::get('/checkout', function () {
    return view('storefront.checkout');
})->name('checkout.index');

Route::post('/checkout/process', function (Request $request) {
    return redirect()->route('home')->with('success', 'Đặt hàng thành công!');
})->name('checkout.process');

// ==========================================
// 4. CUSTOMER ACCOUNT & ORDERS
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/account/profile', function () {
        return view('storefront.account', ['tab' => 'profile']);
    })->name('account.profile');

    Route::get('/account/addresses', function () {
        return view('storefront.account', ['tab' => 'addresses']);
    })->name('account.addresses');

    Route::get('/orders', function () {
        return view('storefront.account', ['tab' => 'orders']);
    })->name('orders.index');
});

// Fallback for guest viewing orders link in header
Route::get('/orders-lookup', function () {
    return view('storefront.account', ['tab' => 'orders']);
})->name('orders.lookup');

// ==========================================
// 5. ADMIN PORTAL & REPORTS
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/revenue/export', [ReportController::class, 'exportRevenueCsv'])->name('reports.revenue.export');

    Route::get('/products', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Danh Mục & Sản Phẩm Nội Thất',
            'icon' => 'fas fa-boxes-stacked',
            'assignedTo' => 'Phụ trách Quản lý Sản phẩm & Tồn kho',
            'description' => 'Xem danh sách sản phẩm, thêm/sửa/xóa thông số kỹ thuật, quản lý giá bán, kho hàng và tải lên hình ảnh.',
        ]);
    })->name('products.index');

    Route::get('/categories', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Danh Mục Nội Thất',
            'icon' => 'fas fa-tags',
            'assignedTo' => 'Phụ trách Quản lý Sản phẩm',
            'description' => 'Tổ chức phân loại danh mục sản phẩm, cấu hình thứ tự hiển thị và phòng trưng bày tương ứng.',
        ]);
    })->name('categories.index');

    Route::get('/orders', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Đơn Hàng & Đổi Trả',
            'icon' => 'fas fa-receipt',
            'assignedTo' => 'Phụ trách Đơn hàng & Thanh toán',
            'description' => 'Theo dõi luồng trạng thái đơn hàng (Chuẩn bị -> Vận chuyển -> Đã giao), xử lý khiếu nại và duyệt yêu cầu đổi trả / hoàn tiền.',
        ]);
    })->name('orders.index');

    Route::get('/vouchers', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Mã Giảm Giá & Voucher',
            'icon' => 'fas fa-ticket-simple',
            'assignedTo' => 'Phụ trách Khuyến mãi & Sản phẩm',
            'description' => 'Thiết lập mã coupon giảm giá theo %, giảm tiền cố định, giới hạn lượt dùng và ngày hết hạn.',
        ]);
    })->name('vouchers.index');

    Route::get('/users', function () {
        return view('admin.placeholder', [
            'title' => 'Quản Lý Thành Viên & Khách Hàng',
            'icon' => 'fas fa-users',
            'assignedTo' => 'Phụ trách Tài khoản & Người dùng',
            'description' => 'Danh sách tài khoản khách hàng VIP/Guest, phân quyền quản trị viên và lịch sử mua sắm tích điểm.',
        ]);
    })->name('users.index');
});
