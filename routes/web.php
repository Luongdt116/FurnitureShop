<?php

use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\OrderController;
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
Route::get('/catalog/search', [StorefrontController::class, 'catalog'])->name('catalog.search');
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

Route::post('/cart/add', function (Request $request) {
    return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
})->name('cart.add');

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

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
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

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/reports/revenue', function () {
        return view('admin.reports.revenue');
    })->name('reports.revenue');

    Route::get('/products', function () {
        return view('admin.products');
    })->name('products.index');

    Route::get('/categories', function () {
        return view('admin.categories');
    })->name('categories.index');

    Route::get('/orders', function () {
        return view('admin.orders');
    })->name('orders.index');

    Route::get('/vouchers', function () {
        return view('admin.vouchers');
    })->name('vouchers.index');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users.index');
});
