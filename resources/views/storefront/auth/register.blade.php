@extends('layouts.storefront')
@section('title', 'Đăng ký | AURA LIVING')
@section('content')
<section class="auth-page" id="auth-page"><div class="auth-page-card"><p class="cart-page-muted">Gia nhập cộng đồng AURA LIVING</p><h1 class="auth-page-title">Tạo tài khoản</h1><p class="auth-page-intro">Lưu thông tin giao hàng và quản lý đơn hàng thuận tiện hơn.</p>
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('register') }}" method="POST" id="auth-register-form">@csrf<input type="hidden" name="redirect" value="{{ request('redirect', url('/checkout')) }}">
<div class="auth-page-field"><label class="form-label" for="auth-register-name">Họ và tên</label><input class="form-control form-input" type="text" id="auth-register-name" name="name" value="{{ old('name') }}" autocomplete="name" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-register-email">Email</label><input class="form-control form-input" type="email" id="auth-register-email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-register-phone">Số điện thoại</label><input class="form-control form-input" type="tel" id="auth-register-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-register-password">Mật khẩu</label><input class="form-control form-input" type="password" id="auth-register-password" name="password" autocomplete="new-password" minlength="8" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-register-confirmation">Xác nhận mật khẩu</label><input class="form-control form-input" type="password" id="auth-register-confirmation" name="password_confirmation" autocomplete="new-password" required></div>
<button class="btn btn-primary w-100" type="submit" id="auth-register-submit">Tạo tài khoản</button></form><p class="auth-page-footer">Đã có tài khoản? <a href="{{ url('/login') }}" id="auth-login-link">Đăng nhập</a></p></div></section>
@endsection
