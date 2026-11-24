@extends('layouts.storefront')
@section('title', 'Đăng nhập | AURA LIVING')
@section('content')
<section class="auth-page" id="auth-page"><div class="auth-page-card"><p class="cart-page-muted">Chào mừng bạn trở lại</p><h1 class="auth-page-title">Đăng nhập</h1><p class="auth-page-intro">Đăng nhập để tiếp tục thanh toán và theo dõi đơn hàng.</p>
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('login') }}" method="POST" id="auth-login-form">@csrf<input type="hidden" name="redirect" value="{{ request('redirect') }}">
<div class="auth-page-field"><label class="form-label" for="auth-login-email">Email</label><input class="form-control form-input" type="email" id="auth-login-email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
<div class="auth-page-field"><label class="form-label" for="auth-login-password">Mật khẩu</label><div class="input-group"><input class="form-control form-input" type="password" id="auth-login-password" name="password" autocomplete="current-password" required><button class="btn btn-outline" type="button" id="auth-password-toggle" aria-controls="auth-login-password" aria-label="Hiện mật khẩu" aria-pressed="false"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div></div>
<div class="auth-page-links"><label class="d-flex gap-2 align-items-center"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label><a href="{{ url('/forgot-password') }}" id="auth-forgot-link">Quên mật khẩu?</a></div><button class="btn btn-primary w-100" type="submit" id="auth-login-submit">Đăng nhập</button></form>
<p class="auth-page-footer">Chưa có tài khoản? <a href="{{ url('/register') }}" id="auth-register-link">Đăng ký</a></p></div></section>
@endsection
@push('scripts')
<script>
    document.getElementById('auth-password-toggle')?.addEventListener('click', function () {
        const input = document.getElementById(this.getAttribute('aria-controls'));
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.setAttribute('aria-pressed', String(show));
        this.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        const icon = this.querySelector('i');
        icon?.classList.toggle('fa-eye', !show);
        icon?.classList.toggle('fa-eye-slash', show);
    });
</script>
@endpush
