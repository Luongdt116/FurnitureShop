@extends('layouts.storefront')
@section('title', 'Thanh toán | AURA LIVING')
@section('content')
<section class="checkout-page" id="checkout-page"><div class="container">
<header class="checkout-page-header"><p class="cart-page-muted">AURA LIVING / Giỏ hàng / Thanh toán</p><h1 class="checkout-page-title">Hoàn tất đơn hàng</h1><p class="cart-page-muted">Thông tin và giá trị đơn hàng sẽ được xác nhận trên máy chủ trước khi tạo đơn.</p></header>
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@guest <div class="alert alert-info" role="status">Vui lòng <a href="{{ url('/login?redirect='.urlencode(url('/checkout'))) }}">đăng nhập</a> để tiếp tục thanh toán.</div> @endguest
@php($items = $cartItems ?? collect())
<form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">@csrf
<div class="checkout-page-layout"><div>
<div class="checkout-page-card"><h2><i class="fa-regular fa-user me-2" aria-hidden="true"></i>Thông tin nhận hàng</h2><div class="checkout-page-address">
<div class="checkout-page-field"><label class="form-label" for="checkout-recipient">Họ tên người nhận</label><input class="form-control form-input" id="checkout-recipient" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name ?? '') }}" autocomplete="name" required></div>
<div class="checkout-page-field"><label class="form-label" for="checkout-phone">Số điện thoại</label><input class="form-control form-input" id="checkout-phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" autocomplete="tel" required></div>
<div class="checkout-page-field"><label class="form-label" for="checkout-province">Tỉnh / Thành phố</label><input class="form-control form-input" id="checkout-province" name="province" value="{{ old('province') }}" autocomplete="address-level1" required></div>
<div class="checkout-page-field"><label class="form-label" for="checkout-district">Quận / Huyện</label><input class="form-control form-input" id="checkout-district" name="district" value="{{ old('district') }}" required></div>
<div class="checkout-page-field"><label class="form-label" for="checkout-ward">Phường / Xã</label><input class="form-control form-input" id="checkout-ward" name="ward" value="{{ old('ward') }}" required></div>
<div class="checkout-page-field"><label class="form-label" for="checkout-address">Địa chỉ cụ thể</label><input class="form-control form-input" id="checkout-address" name="address_line" value="{{ old('address_line') }}" autocomplete="street-address" required></div>
</div><div class="checkout-page-field mb-0"><label class="form-label" for="checkout-note">Ghi chú giao hàng <span class="cart-page-muted">(không bắt buộc)</span></label><textarea class="form-control form-input" id="checkout-note" name="note" rows="3">{{ old('note') }}</textarea></div></div>
<div class="checkout-page-card"><h2><i class="fa-regular fa-credit-card me-2" aria-hidden="true"></i>Phương thức thanh toán</h2>
<label class="checkout-page-payment" data-checkout-payment><input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod') required><span><strong>Thanh toán khi nhận hàng</strong><br><small class="cart-page-muted">Thanh toán sau khi nhận và kiểm tra sản phẩm.</small></span></label>
<label class="checkout-page-payment" data-checkout-payment><input type="radio" name="payment_method" value="online_demo" @checked(old('payment_method') === 'online_demo')><span><strong>Thanh toán trực tuyến</strong><br><small class="cart-page-muted">Bạn sẽ được chuyển đến bước thanh toán an toàn.</small></span></label>
</div></div>
<aside class="checkout-page-summary"><h2>Đơn hàng của bạn</h2>@forelse($items as $item)@php($product = $item->product ?? null)<div class="checkout-page-line"><img src="{{ asset('storage/'.($product?->image ?? $product?->primaryImage?->path ?? 'images/product-placeholder.webp')) }}" alt=""><div class="checkout-page-line-info"><strong>{{ $product?->name ?? 'Sản phẩm' }}</strong><div class="cart-page-muted">Số lượng: {{ $item->quantity }}</div></div><strong>{{ number_format(($product?->final_price ?? $product?->price ?? 0) * $item->quantity, 0, ',', '.') }}₫</strong></div>@empty<p class="cart-page-muted">Chưa có sản phẩm trong giỏ.</p>@endforelse
<div class="checkout-page-summary-row mt-3"><span>Tạm tính</span><strong>{{ number_format($subtotal ?? $items->sum(fn($item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity), 0, ',', '.') }}₫</strong></div><div class="checkout-page-summary-row"><span>Giao hàng</span><span>Sẽ xác nhận</span></div><div class="checkout-page-summary-total"><span>Tổng cộng</span><span>{{ number_format($subtotal ?? $items->sum(fn($item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity), 0, ',', '.') }}₫</span></div><button class="btn btn-primary w-100 mt-4" type="submit" id="checkout-submit">Đặt hàng</button><p class="cart-page-muted text-center mt-3 mb-0">Thông tin thanh toán được xử lý an toàn.</p></aside>
</div></form></div></section>
@endsection
