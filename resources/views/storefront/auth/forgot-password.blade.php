@extends('layouts.storefront')
@section('title', 'Đặt lại mật khẩu | AURA LIVING')
@section('content')
<section class="auth-page" id="auth-page"><div class="auth-page-card"><h1 class="auth-page-title">Quên mật khẩu?</h1><p class="auth-page-intro">Nhập email đã đăng ký để nhận hướng dẫn đặt lại mật khẩu.</p>
@if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('password.email') }}" method="POST" id="auth-forgot-form">@csrf<div class="auth-page-field"><label class="form-label" for="auth-forgot-email">Email</label><input class="form-control form-input" type="email" id="auth-forgot-email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div><button class="btn btn-primary w-100" type="submit" id="auth-forgot-submit">Gửi hướng dẫn</button></form><p class="auth-page-footer"><a href="{{ route('login.show') }}" id="auth-back-login">Quay lại đăng nhập</a></p></div></section>
@endsection
