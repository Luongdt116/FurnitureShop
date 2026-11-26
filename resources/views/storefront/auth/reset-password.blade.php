@extends('layouts.storefront')
@section('title', 'Tạo mật khẩu mới | AURA LIVING')
@section('content')
<section class="auth-page" id="auth-page"><div class="auth-page-card"><p class="cart-page-muted">Bảo mật tài khoản</p><h1 class="auth-page-title">Tạo mật khẩu mới</h1><p class="auth-page-intro">Chọn mật khẩu mới để tiếp tục sử dụng tài khoản.</p>
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('password.update') }}" method="POST" id="auth-reset-form">@csrf<input type="hidden" name="token" value="{{ $token }}">
<div class="auth-page-field"><label class="form-label" for="auth-reset-email">Email</label><input class="form-control form-input" type="email" id="auth-reset-email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-reset-password">Mật khẩu mới</label><input class="form-control form-input" type="password" id="auth-reset-password" name="password" autocomplete="new-password" minlength="8" required></div>
<div class="auth-page-field"><label class="form-label" for="auth-reset-confirmation">Xác nhận mật khẩu mới</label><input class="form-control form-input" type="password" id="auth-reset-confirmation" name="password_confirmation" autocomplete="new-password" required></div>
<button class="btn btn-primary w-100" type="submit" id="auth-reset-submit">Cập nhật mật khẩu</button></form></div></section>
@endsection
