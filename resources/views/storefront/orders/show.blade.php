@extends('layouts.storefront')

@section('title', 'Chi tiết đơn hàng ' . $order->order_code . ' - AURA LIVING')

@section('content')
@php
    $statusLabels = [
        'preparing' => 'Đang chuẩn bị',
        'awaiting_shipment' => 'Đang chờ vận chuyển',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao thành công',
        'cancelled' => 'Đã hủy',
    ];
    $statusVariant = match ($order->status) {
        'delivered' => 'success',
        'cancelled' => 'danger',
        'shipping', 'awaiting_shipment' => 'warning',
        default => 'gold',
    };
    $latestPayment = $order->payments->first();
    $paymentExpiresAt = $order->payment_expires_at;
    $onlinePayment = in_array($order->payment_method, ['online_demo', 'vnpay', 'vietqr'], true);
    $canRequestCancellation = in_array($order->status, ['preparing', 'awaiting_shipment'], true);
    $paymentMethodLabels = [
        'cod' => 'Thanh toán khi nhận hàng',
        'online_demo' => 'Thanh toán trực tuyến',
        'vnpay' => 'VNPay',
        'vietqr' => 'VietQR',
    ];
    $paymentStatus = $latestPayment?->status ?? $order->payment_status;
    $paymentStatusLabels = [
        'pending' => 'Đang chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'refunded' => 'Đã hoàn tiền',
        'cancelled' => 'Đã hủy',
    ];
@endphp

<section class="orders-page order-detail-page section-padding" id="orders-detail-page">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a href="{{ route('orders.index') }}" class="btn btn-ghost ps-0" id="orders-back-to-list">
                <i class="fas fa-arrow-left"></i> Quay lại đơn hàng
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                <x-badge :variant="$statusVariant" id="orders-detail-status">{{ $statusLabels[$order->status] ?? $order->status }}</x-badge>
            </div>
        </div>

        <div class="order-detail-heading mb-4" id="orders-detail-heading">
            <div>
                <span class="section-eyebrow">Thông tin đơn hàng</span>
                <h1 class="section-title mb-1">{{ $order->order_code }}</h1>
                <p class="text-muted mb-0">Theo dõi trạng thái, phương thức thanh toán và thông tin giao nhận.</p>
            </div>
            @if ($canRequestCancellation)
                <button type="button" class="btn btn-outline-danger" id="cancel-open-modal" onclick="openAuraModal('cancel-modal')">
                    <i class="fas fa-ban"></i> Yêu cầu hủy đơn
                </button>
            @endif
        </div>

        <div class="order-detail-grid">
            <div class="order-detail-main">
                <section class="order-detail-panel" id="tracking-status-panel">
                    <div class="order-panel-heading">
                        <div>
                            <span class="section-eyebrow">Cập nhật đơn hàng</span>
                            <h2>Theo dõi đơn hàng</h2>
                        </div>
                        @if ($order->estimated_delivery_date)
                            <span class="tracking-estimate">Dự kiến giao {{ $order->estimated_delivery_date->format('d/m/Y') }}</span>
                        @endif
                    </div>

                    <ol class="order-status-steps" id="tracking-status-steps">
                        @php
                            $steps = ['preparing', 'awaiting_shipment', 'shipping', 'delivered'];
                            $currentStep = array_search($order->status, $steps, true);
                        @endphp
                        @if ($order->status === 'cancelled')
                            <li class="order-status-step is-cancelled">
                                <span class="order-status-dot"><i class="fas fa-xmark"></i></span>
                                <div><strong>Đã hủy</strong><small>{{ $order->cancelled_at?->format('d/m/Y H:i') }}</small></div>
                            </li>
                        @else
                            @foreach ($steps as $index => $step)
                                <li class="order-status-step {{ $currentStep !== false && $index <= $currentStep ? 'is-complete' : '' }} {{ $order->status === $step ? 'is-current' : '' }}" id="tracking-step-{{ $step }}">
                                    <span class="order-status-dot">
                                        @if ($currentStep !== false && $index < $currentStep)<i class="fas fa-check"></i>@else{{ $index + 1 }}@endif
                                    </span>
                                    <div>
                                        <strong>{{ $statusLabels[$step] }}</strong>
                                        @if ($order->status === $step)<small>Trạng thái hiện tại</small>@endif
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ol>

                    @if ($order->tracking_code || $order->shipping_carrier)
                        <div class="tracking-summary" id="tracking-shipment-details">
                            @if ($order->shipping_carrier)
                                <div><span>Đơn vị vận chuyển</span><strong>{{ $order->shipping_carrier }}</strong></div>
                            @endif
                            @if ($order->tracking_code)
                                <div><span>Mã vận đơn</span><strong id="tracking-code">{{ $order->tracking_code }}</strong></div>
                            @endif
                        </div>
                    @endif
                </section>

                <section class="order-detail-panel" id="orders-items-panel">
                    <div class="order-panel-heading">
                        <div>
                            <span class="section-eyebrow">Sản phẩm trong đơn</span>
                            <h2>Chi tiết sản phẩm</h2>
                        </div>
                        <span class="text-muted">{{ $order->items->sum('quantity') }} sản phẩm</span>
                    </div>

                    <div class="order-detail-items">
                        @foreach ($order->items as $item)
                            <article class="order-detail-item" id="orders-detail-item-{{ $item->id }}">
                                @if ($item->product_image)
                                    @php
                                        $image = $item->product_image;
                                        $imageUrl = \Illuminate\Support\Str::startsWith($image, ['http://', 'https://', '/'])
                                            ? $image
                                            : \Illuminate\Support\Facades\Storage::url($image);
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="order-detail-item-image" loading="lazy">
                                @else
                                    <div class="order-detail-item-image order-item-thumb-empty" aria-hidden="true"><i class="fas fa-couch"></i></div>
                                @endif
                                <div class="order-detail-item-info">
                                    <h3>{{ $item->product_name }}</h3>
                                    @if ($item->sku)<span class="text-muted small">Mã SP: {{ $item->sku }}</span>@endif
                                    <span class="text-muted small">Đơn giá: {{ number_format((float) $item->unit_price, 0, ',', '.') }}₫</span>
                                </div>
                                <div class="order-detail-item-total">
                                    <span>Số lượng: {{ $item->quantity }}</span>
                                    <strong>{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</strong>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                @if ($order->status === 'delivered')
                    <section class="order-detail-panel" id="return-create-panel">
                        <div class="order-panel-heading">
                            <div>
                                <span class="section-eyebrow">Hỗ trợ sau mua hàng</span>
                                <h2>Đổi trả hoặc hoàn tiền</h2>
                            </div>
                            <a href="{{ route('orders.returns.index', $order) }}" class="btn btn-ghost btn-sm" id="return-view-all">Yêu cầu đã gửi</a>
                        </div>
                        <p class="text-muted small">Chọn sản phẩm và số lượng. Có thể gửi nhiều yêu cầu riêng cho cùng một đơn hàng.</p>
                        <form action="{{ route('orders.returns.store', $order) }}" method="POST" enctype="multipart/form-data" id="return-form">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label" for="return-type">Hình thức yêu cầu <span class="text-danger">*</span></label>
                                    <select name="return_type" id="return-type" class="form-select form-input" required>
                                        <option value="refund" @selected(old('return_type') === 'refund')>Hoàn tiền</option>
                                        <option value="exchange" @selected(old('return_type') === 'exchange')>Đổi sản phẩm</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label" for="return-evidence">Ảnh bằng chứng (không bắt buộc)</label>
                                    <input type="file" name="evidence" id="return-evidence" class="form-control form-input" accept="image/jpeg,image/png,image/webp">
                                </div>
                            </div>

                            <div class="return-select-items mb-3">
                                @foreach ($order->items as $index => $item)
                                    <div class="return-select-item" id="return-select-item-{{ $item->id }}">
                                        <div>
                                            <strong>{{ $item->product_name }}</strong>
                                            <span>Còn có thể yêu cầu: {{ $returnableQuantities[$item->id] ?? 0 }} / {{ $item->quantity }}</span>
                                        </div>
                                        <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                        <label for="return-quantity-{{ $item->id }}" class="visually-hidden">Số lượng đổi/trả cho {{ $item->product_name }}</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" id="return-quantity-{{ $item->id }}" class="form-control form-input return-quantity-input" min="0" max="{{ $returnableQuantities[$item->id] ?? 0 }}" value="{{ old("items.$index.quantity", 0) }}" aria-describedby="return-quantity-help-{{ $item->id }}">
                                        <small id="return-quantity-help-{{ $item->id }}">0 = không chọn</small>
                                    </div>
                                @endforeach
                            </div>

                            <label for="return-reason" class="form-label">Lý do đổi/trả <span class="text-danger">*</span></label>
                            <textarea name="reason" id="return-reason" class="form-control form-textarea mb-3" rows="4" maxlength="5000" required placeholder="Mô tả lý do bạn muốn đổi hoặc trả sản phẩm">{{ old('reason') }}</textarea>
                            <button type="submit" class="btn btn-accent" id="return-submit">
                                <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                            </button>
                        </form>
                    </section>

                    <section class="order-detail-panel" id="review-create-panel">
                        <div class="order-panel-heading">
                            <div><span class="section-eyebrow">Chia sẻ trải nghiệm</span><h2>Đánh giá sản phẩm</h2></div>
                        </div>
                        <div class="review-order-items">
                            @foreach ($order->items as $item)
                                <article class="review-order-item" id="review-order-item-{{ $item->id }}">
                                    <div class="review-order-item-heading">
                                        <strong>{{ $item->product_name }}</strong>
                                        @if ($item->review)
                                            <x-badge variant="success">Đã đánh giá</x-badge>
                                        @endif
                                    </div>
                                    @if ($item->review)
                                        <p class="mb-1" aria-label="{{ $item->review->rating }} trên 5 sao">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <i class="{{ $star <= $item->review->rating ? 'fas' : 'far' }} fa-star review-star"></i>
                                            @endfor
                                        </p>
                                        @if ($item->review->comment)<p class="text-muted mb-1">{{ $item->review->comment }}</p>@endif
                                        @if ($item->review->admin_reply)<div class="review-admin-reply"><strong>Phản hồi từ cửa hàng</strong><p class="mb-0">{{ $item->review->admin_reply }}</p></div>@endif
                                    @else
                                        <form action="{{ route('reviews.store', [$order, $item]) }}" method="POST" enctype="multipart/form-data" class="review-form" id="review-form-{{ $item->id }}">
                                            @csrf
                                            <div class="row g-3 align-items-end">
                                                <div class="col-sm-4">
                                                    <label for="review-rating-{{ $item->id }}" class="form-label">Số sao</label>
                                                    <select name="rating" id="review-rating-{{ $item->id }}" class="form-select form-input" required>
                                                        <option value="">Chọn sao</option>
                                                        @for ($rating = 5; $rating >= 1; $rating--)
                                                            <option value="{{ $rating }}">{{ $rating }} sao</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-sm-8">
                                                    <label for="review-image-{{ $item->id }}" class="form-label">Ảnh (không bắt buộc)</label>
                                                    <input type="file" name="image" id="review-image-{{ $item->id }}" class="form-control form-input" accept="image/jpeg,image/png,image/webp">
                                                </div>
                                                <div class="col-12">
                                                    <label for="review-comment-{{ $item->id }}" class="form-label">Nhận xét</label>
                                                    <textarea name="comment" id="review-comment-{{ $item->id }}" class="form-control form-textarea" rows="3" maxlength="5000" placeholder="Chia sẻ cảm nhận của bạn"></textarea>
                                                </div>
                                                <div class="col-12"><button type="submit" class="btn btn-outline btn-sm" id="review-submit-{{ $item->id }}">Gửi đánh giá</button></div>
                                            </div>
                                        </form>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="order-detail-panel" id="tracking-history-panel">
                    <div class="order-panel-heading">
                        <div>
                            <span class="section-eyebrow">Các lần cập nhật</span>
                            <h2>Lịch sử trạng thái</h2>
                        </div>
                    </div>
                    @if ($order->statusHistories->isNotEmpty())
                        <ol class="tracking-history-list" id="tracking-history-list">
                            @foreach ($order->statusHistories->sortBy('created_at') as $history)
                                <li class="tracking-history-item" id="tracking-history-{{ $history->id }}">
                                    <span class="tracking-history-marker"></span>
                                    <div class="tracking-history-content">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <strong>{{ $statusLabels[$history->to_status] ?? $history->to_status }}</strong>
                                            <time datetime="{{ $history->created_at?->toIso8601String() }}">{{ $history->created_at?->format('d/m/Y H:i') }}</time>
                                        </div>
                                        <p class="mb-0 text-muted small">
                                            @if ($history->changedBy)
                                                Cập nhật bởi {{ $history->changedBy->name }}
                                            @elseif ($history->actor_type === 'system')
                                                Cập nhật tự động bởi hệ thống
                                            @elseif ($history->actor_type === 'customer')
                                                Cập nhật bởi khách hàng
                                            @else
                                                Cập nhật bởi nhân viên
                                            @endif
                                            @if ($history->reason) · {{ $history->reason }} @endif
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted mb-0" id="tracking-history-empty">Chưa có cập nhật trạng thái nào.</p>
                    @endif
                </section>
            </div>

            <aside class="order-detail-aside">
                <section class="order-detail-panel" id="orders-shipping-address">
                    <div class="order-panel-heading">
                        <div><span class="section-eyebrow">Giao đến</span><h2>Thông tin nhận hàng</h2></div>
                    </div>
                    <div class="order-address-card">
                        <strong>{{ $order->recipient_name }}</strong>
                        <span><i class="fas fa-phone"></i> {{ $order->phone }}</span>
                        <span><i class="fas fa-location-dot"></i> {{ $order->address }}</span>
                    </div>
                </section>

                <section class="order-detail-panel" id="orders-payment-panel">
                    <div class="order-panel-heading">
                        <div><span class="section-eyebrow">Thanh toán</span><h2>Thông tin thanh toán</h2></div>
                    </div>
                    <dl class="order-payment-details">
                        <div><dt>Phương thức</dt><dd>{{ $paymentMethodLabels[$order->payment_method] ?? $order->payment_method }}</dd></div>
                        <div><dt>Trạng thái</dt><dd>{{ $paymentStatusLabels[$paymentStatus] ?? $paymentStatus }}</dd></div>
                        @if ($latestPayment?->transaction_code)
                            <div><dt>Mã giao dịch</dt><dd>{{ $latestPayment->transaction_code }}</dd></div>
                        @endif
                        @if ($onlinePayment && $paymentExpiresAt)
                            <div class="order-payment-expiry" id="orders-payment-expiry">
                                <dt>Thanh toán trước</dt>
                                <dd>{{ $paymentExpiresAt->format('d/m/Y H:i') }}</dd>
                                <small>Thời hạn này lấy từ thông tin thanh toán của đơn hàng.</small>
                            </div>
                        @elseif ($onlinePayment && $order->payment_status === 'pending')
                            <div class="order-payment-pending" id="orders-payment-pending-note">
                                <dt>Trạng thái thanh toán</dt>
                                <dd>Đang chờ thanh toán</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <section class="order-detail-panel" id="orders-total-panel">
                    <div class="order-panel-heading"><div><span class="section-eyebrow">Tổng quan</span><h2>Tổng thanh toán</h2></div></div>
                    <dl class="order-total-lines">
                        <div><dt>Tạm tính</dt><dd>{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</dd></div>
                        <div><dt>Phí vận chuyển</dt><dd>{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫</dd></div>
                        @if ((float) $order->discount_amount > 0)
                            <div><dt>Ưu đãi</dt><dd>-{{ number_format((float) $order->discount_amount, 0, ',', '.') }}₫</dd></div>
                        @endif
                        <div class="order-grand-total"><dt>Tổng cộng</dt><dd>{{ number_format((float) $order->total, 0, ',', '.') }}₫</dd></div>
                    </dl>
                </section>

                @if ($order->customer_note)
                    <section class="order-detail-panel" id="orders-customer-note">
                        <div class="order-panel-heading"><div><span class="section-eyebrow">Ghi chú</span><h2>Lời nhắn của bạn</h2></div></div>
                        <p class="mb-0">{{ $order->customer_note }}</p>
                    </section>
                @endif

                @if ($order->status === 'cancelled')
                    <section class="order-cancelled-notice" id="cancel-order-result">
                        <h2><i class="fas fa-circle-xmark"></i> Đơn hàng đã hủy</h2>
                        <p>Người hủy: {{ $order->cancelled_by === 'customer' ? 'Khách hàng' : ($order->cancelled_by === 'admin' ? 'Quản trị viên' : 'Hệ thống') }}</p>
                        @if ($order->cancelled_at)<p>Thời điểm: {{ $order->cancelled_at->format('d/m/Y H:i') }}</p>@endif
                        <p class="mb-0">Lý do: {{ $order->cancel_reason ?: 'Chưa có thông tin lý do.' }}</p>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</section>

@if ($canRequestCancellation)
    <x-modal id="cancel-modal" title="Yêu cầu hủy đơn hàng" size="sm">
        <p class="text-muted">Vui lòng cho biết lý do hủy đơn <strong>{{ $order->order_code }}</strong>.</p>
        <form action="{{ route('orders.cancel', $order) }}" method="POST" id="cancel-form">
            @csrf
            <x-form.textarea
                name="reason"
                id="cancel-reason"
                label="Lý do hủy"
                placeholder="Nhập lý do hủy đơn hàng"
                :required="true"
                :rows="4"
                :value="old('reason')"
            />
            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-ghost" onclick="closeAuraModal('cancel-modal')">Quay lại</button>
                <button type="submit" class="btn btn-danger" id="cancel-submit">
                    <i class="fas fa-ban"></i> Gửi yêu cầu hủy
                </button>
            </div>
        </form>
    </x-modal>
@endif
@endsection
