@extends('layouts.storefront')
@section('title', 'Giỏ hàng | AURA LIVING')
@section('content')
<section class="cart-page" id="cart-page"><div class="container">
    <header class="cart-page-header"><p class="cart-page-muted">AURA LIVING / Giỏ hàng</p><h1 class="cart-page-title">Giỏ hàng của bạn</h1><p class="cart-page-muted">Kiểm tra sản phẩm và số lượng trước khi tiếp tục.</p></header>
    @if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @php($items = $cartItems ?? collect())
    @if ($items->isEmpty())
        <div class="cart-page-list"><div class="cart-page-empty"><div class="cart-page-empty-icon"><i class="fa-solid fa-basket-shopping" aria-hidden="true"></i></div><h2>Giỏ hàng đang trống</h2><p class="cart-page-muted">Hãy khám phá bộ sưu tập để tìm món đồ phù hợp cho không gian sống.</p><a class="btn btn-primary mt-3" href="{{ route('catalog.index') }}" id="cart-continue-shopping">Khám phá sản phẩm</a></div></div>
    @else
        <div class="cart-page-layout"><div class="cart-page-list" aria-label="Sản phẩm trong giỏ">
            @foreach ($items as $item)
                @php($product = $item->product ?? null)
                <article class="cart-page-item" id="cart-item-{{ $item->id }}">
                    <img class="cart-page-image" src="{{ asset('storage/'.($product?->image ?? $product?->primaryImage?->path ?? 'images/product-placeholder.webp')) }}" alt="{{ $product?->name ?? 'Sản phẩm' }}">
                    <div><h2 class="cart-page-item-title">{{ $product?->name ?? 'Sản phẩm không còn khả dụng' }}</h2><p class="cart-page-item-meta mb-2">{{ $product?->sku ?? '' }}</p>
                        <form action="{{ url('/cart/items/'.$item->id) }}" method="POST" class="d-flex align-items-center gap-3 flex-wrap">@csrf @method('PATCH')
                            <label class="cart-page-muted" for="cart-quantity-{{ $item->id }}">Số lượng</label><div class="cart-page-quantity" data-cart-quantity><button type="button" aria-label="Giảm số lượng" data-cart-quantity-step="-1">−</button><input id="cart-quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ max(1, $product?->stock ?? 999) }}" value="{{ $item->quantity }}" required><button type="button" aria-label="Tăng số lượng" data-cart-quantity-step="1">+</button></div><button class="btn btn-outline btn-sm" type="submit">Cập nhật</button>
                        </form>
                    </div><div class="cart-page-item-price">{{ number_format(($product?->final_price ?? $product?->price ?? 0) * $item->quantity, 0, ',', '.') }}₫
                        <form class="mt-2" action="{{ url('/cart/items/'.$item->id) }}" method="POST" data-confirm-remove>@csrf @method('DELETE')<button class="cart-page-muted" type="submit" id="cart-remove-{{ $item->id }}"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Xóa</button></form>
                    </div>
                </article>
            @endforeach
            <div class="cart-page-actions"><a class="btn btn-outline" href="{{ route('catalog.index') }}" id="cart-continue-shopping"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Tiếp tục mua sắm</a><form action="{{ route('cart.clear') }}" method="POST" data-confirm-remove>@csrf<button class="btn btn-outline" type="submit" id="cart-clear">Xóa toàn bộ giỏ</button></form></div>
        </div><aside class="cart-page-summary" aria-labelledby="cart-summary-title"><h2 id="cart-summary-title">Tóm tắt đơn hàng</h2><div class="cart-page-summary-row"><span>Tạm tính</span><strong>{{ number_format($subtotal ?? $items->sum(fn($item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity), 0, ',', '.') }}₫</strong></div><p class="cart-page-muted">Phí giao hàng sẽ được xác nhận tại bước thanh toán.</p><div class="cart-page-summary-total"><span>Tổng cộng</span><span>{{ number_format($subtotal ?? $items->sum(fn($item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity), 0, ',', '.') }}₫</span></div><a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 mt-4" id="cart-checkout">Tiến hành thanh toán <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></aside></div>
    @endif
</div></section>
@endsection
