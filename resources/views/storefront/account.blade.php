@extends('layouts.storefront')
@section('title', 'Tài khoản | AURA LIVING')
@section('content')
@php($activeTab = $tab ?? 'profile')
<section class="account-page" id="account-page"><div class="container"><header class="account-page-header"><p class="cart-page-muted">AURA LIVING / Tài khoản</p><h1 class="account-page-title">Không gian của tôi</h1></header>
@if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="account-page-layout"><aside class="account-page-sidebar"><div class="account-user-card"><div class="account-avatar d-grid place-items-center" aria-hidden="true"><i class="fa-regular fa-user"></i></div><strong>{{ auth()->user()->name ?? 'Tài khoản khách hàng' }}</strong><div class="cart-page-muted">{{ auth()->user()->email ?? '' }}</div></div><nav class="account-page-nav" aria-label="Điều hướng tài khoản">
<a href="{{ route('account.profile') }}" aria-current="{{ $activeTab === 'profile' ? 'page' : 'false' }}" id="account-nav-profile"><i class="fa-regular fa-id-card me-2" aria-hidden="true"></i>Hồ sơ</a>
<a href="{{ route('account.addresses') }}" aria-current="{{ $activeTab === 'addresses' ? 'page' : 'false' }}" id="account-nav-addresses"><i class="fa-solid fa-location-dot me-2" aria-hidden="true"></i>Địa chỉ giao hàng</a>
<a href="{{ route('orders.index') }}" aria-current="{{ $activeTab === 'orders' ? 'page' : 'false' }}" id="account-nav-orders"><i class="fa-solid fa-box me-2" aria-hidden="true"></i>Đơn hàng</a>
</nav></aside>
<div class="account-page-panel">
@if ($activeTab === 'profile')
<h2 class="h4 mb-4">Thông tin cá nhân</h2><form action="{{ url('/account/profile') }}" method="POST" id="account-profile-form">@csrf @method('PATCH')<div class="account-page-form-grid">
<div class="form-group mb-3"><label class="form-label" for="account-name">Họ và tên</label><input class="form-control form-input" id="account-name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" autocomplete="name" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-email">Email</label><input class="form-control form-input" id="account-email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" autocomplete="email" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-phone">Số điện thoại</label><input class="form-control form-input" id="account-phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" autocomplete="tel"></div>
</div><button class="btn btn-primary mt-2" type="submit" id="account-profile-save">Lưu thay đổi</button></form>
<hr class="my-4"><h3 class="h5 mb-3">Đổi mật khẩu</h3><form action="{{ url('/account/password') }}" method="POST" id="account-password-form">@csrf @method('PUT')<div class="account-page-form-grid">
<div class="form-group mb-3"><label class="form-label" for="account-current-password">Mật khẩu hiện tại</label><input class="form-control form-input" id="account-current-password" name="current_password" type="password" autocomplete="current-password" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-new-password">Mật khẩu mới</label><input class="form-control form-input" id="account-new-password" name="password" type="password" autocomplete="new-password" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-password-confirmation">Xác nhận mật khẩu mới</label><input class="form-control form-input" id="account-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
</div><button class="btn btn-outline mt-2" type="submit" id="account-password-save">Cập nhật mật khẩu</button></form>
@elseif ($activeTab === 'addresses')
<h2 class="h4 mb-4">Địa chỉ giao hàng</h2>@php($addresses = $addresses ?? collect())
@forelse($addresses as $address)<article class="account-page-order"><div class="d-flex justify-content-between gap-3"><div><strong>{{ $address->recipient_name ?? auth()->user()->name }}</strong> <span class="badge bg-light text-dark">{{ $address->is_default ? 'Mặc định' : 'Địa chỉ' }}</span><p class="mb-1 mt-2">{{ $address->address_line ?? '' }}, {{ $address->ward ?? '' }}, {{ $address->district ?? '' }}, {{ $address->province ?? '' }}</p><small class="cart-page-muted">{{ $address->phone ?? '' }}</small></div><form action="{{ url('/account/addresses/'.$address->id) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-outline btn-sm" id="account-address-delete-{{ $address->id }}">Xóa</button></form></div></article>@empty<p class="cart-page-muted">Bạn chưa lưu địa chỉ giao hàng nào.</p>@endforelse
<h3 class="h5 mt-4 mb-3">Thêm địa chỉ</h3><form action="{{ url('/account/addresses') }}" method="POST" id="account-address-form">@csrf<div class="account-page-form-grid">
<div class="form-group mb-3"><label class="form-label" for="account-address-recipient">Người nhận</label><input class="form-control form-input" name="recipient_name" id="account-address-recipient" value="{{ old('recipient_name', auth()->user()->name ?? '') }}" required></div><div class="form-group mb-3"><label class="form-label" for="account-address-phone">Số điện thoại</label><input class="form-control form-input" name="phone" id="account-address-phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-address-province">Tỉnh / Thành phố</label><input class="form-control form-input" name="province" id="account-address-province" required></div><div class="form-group mb-3"><label class="form-label" for="account-address-district">Quận / Huyện</label><input class="form-control form-input" name="district" id="account-address-district" required></div>
<div class="form-group mb-3"><label class="form-label" for="account-address-ward">Phường / Xã</label><input class="form-control form-input" name="ward" id="account-address-ward" required></div><div class="form-group mb-3"><label class="form-label" for="account-address-line">Địa chỉ cụ thể</label><input class="form-control form-input" name="address_line" id="account-address-line" required></div>
</div><label class="d-flex gap-2 align-items-center mb-3"><input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định</label><button class="btn btn-primary" type="submit" id="account-address-save">Lưu địa chỉ</button></form>
@elseif ($activeTab === 'orders')
<h2 class="h4 mb-4">Đơn hàng của tôi</h2>@php($orders = $orders ?? collect())
@forelse($orders as $order)<article class="account-page-order" id="account-order-{{ $order->id }}"><div class="d-flex justify-content-between flex-wrap gap-2"><strong>Mã đơn {{ $order->order_code ?? '#'.$order->id }}</strong><span class="badge bg-light text-dark">{{ $order->status_label ?? $order->status }}</span></div><div class="d-flex justify-content-between mt-2"><span class="cart-page-muted">{{ optional($order->created_at)->format('d/m/Y') }}</span><strong>{{ number_format($order->total ?? 0, 0, ',', '.') }}₫</strong></div></article>@empty<p class="cart-page-muted">Bạn chưa có đơn hàng nào.</p>@endforelse
@else<h2 class="h4">Tài khoản</h2><p class="cart-page-muted">Chọn một mục trong menu để quản lý tài khoản của bạn.</p>@endif
</div></div></div></section>
@endsection
