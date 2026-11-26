@extends('layouts.admin')

@section('title', 'Đơn hàng ' . $order->order_code)

@section('content')
@php
    $currentStatus = $statusLabels[$order->status] ?? $order->status;
    $statusVariant = match ($order->status) {
        'delivered' => 'success',
        'cancelled' => 'danger',
        'shipping', 'awaiting_shipment' => 'warning',
        default => 'gold',
    };
    $latestPayment = $order->payments->first();
    $returnStatusLabels = [
        'requested' => 'Chờ xử lý',
        'approved' => 'Đã duyệt',
        'received' => 'Đã nhận hàng',
        'inspected' => 'Đã kiểm tra',
        'rejected' => 'Từ chối',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];
    $nextStatusLabels = [
        'awaiting_shipment' => 'Đang chờ vận chuyển',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao thành công',
    ];
    $canRequestCancellation = in_array($order->status, ['preparing', 'awaiting_shipment', 'shipping'], true);
@endphp
<section class="admin-order-detail-page" id="admin-order-detail-page">
    <div class="admin-page-heading admin-order-detail-heading">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost ps-0 mb-2" id="admin-order-back"><i class="fas fa-arrow-left"></i> Danh sách đơn</a>
            <h1 class="admin-page-title">{{ $order->order_code }}</h1>
            <p class="text-muted mb-0">Đặt lúc {{ $order->created_at?->format('d/m/Y H:i') }} · {{ $currentStatus }}</p>
        </div>
        <x-badge :variant="$statusVariant" id="admin-order-current-status">{{ $currentStatus }}</x-badge>
    </div>

    <div class="admin-order-detail-grid">
        <div class="admin-order-main-column">
            <section class="admin-card admin-order-panel" id="admin-order-items-panel">
                <div class="admin-order-panel-heading"><h2>Sản phẩm trong đơn</h2><span>{{ $order->items->sum('quantity') }} sản phẩm</span></div>
                <div class="data-table-wrap">
                    <table class="data-table" id="admin-order-items-table">
                        <thead><tr><th>Sản phẩm</th><th>SKU</th><th>Số lượng</th><th>Đơn giá snapshot</th><th>Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr id="admin-order-item-{{ $item->id }}">
                                    <td><strong>{{ $item->product_name }}</strong></td>
                                    <td>{{ $item->sku ?: '—' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}₫</td>
                                    <td>{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <dl class="admin-order-totals">
                    <div><dt>Tạm tính</dt><dd>{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</dd></div>
                    <div><dt>Phí vận chuyển</dt><dd>{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫</dd></div>
                    <div><dt>Giảm giá</dt><dd>-{{ number_format((float) $order->discount_amount, 0, ',', '.') }}₫</dd></div>
                    <div class="admin-order-grand-total"><dt>Tổng cộng</dt><dd>{{ number_format((float) $order->total, 0, ',', '.') }}₫</dd></div>
                </dl>
            </section>

            <section class="admin-card admin-order-panel" id="admin-order-history-panel">
                <div class="admin-order-panel-heading"><h2>Lịch sử trạng thái</h2></div>
                @if ($order->statusHistories->isNotEmpty())
                    <ol class="admin-order-history" id="admin-order-history">
                        @foreach ($order->statusHistories->sortBy('created_at') as $history)
                            <li id="admin-order-history-{{ $history->id }}">
                                <strong>{{ $statusLabels[$history->to_status] ?? $history->to_status }}</strong>
                                <time>{{ $history->created_at?->format('d/m/Y H:i') }}</time>
                                <span>{{ $history->changedBy?->name ?? ($history->actor_type === 'system' ? 'Hệ thống' : $history->actor_type) }}@if($history->reason) · {{ $history->reason }}@endif</span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-muted mb-0">Chưa có lịch sử trạng thái.</p>
                @endif
            </section>

            <section class="admin-card admin-order-panel" id="admin-order-returns-panel">
                <div class="admin-order-panel-heading"><h2>Yêu cầu đổi trả / hoàn tiền</h2><span>{{ $order->returnRequests->count() }} yêu cầu</span></div>
                @forelse ($order->returnRequests as $returnRequest)
                    @php $availableReturnStatuses = $returnTransitions[$returnRequest->status] ?? []; @endphp
                    <article class="admin-return-request" id="admin-order-return-{{ $returnRequest->id }}">
                        <div class="admin-return-heading">
                            <div><strong>{{ $returnRequest->return_code }}</strong><small>{{ $returnRequest->user?->name }} · {{ $returnRequest->requested_at?->format('d/m/Y H:i') }}</small></div>
                            <x-badge :variant="match ($returnRequest->status) { 'completed' => 'success', 'rejected', 'cancelled' => 'danger', 'approved', 'received', 'inspected' => 'warning', default => 'gold' }">
                                {{ $returnStatusLabels[$returnRequest->status] ?? $returnRequest->status }}
                            </x-badge>
                        </div>
                        <p><strong>Hình thức:</strong> {{ $returnRequest->return_type === 'exchange' ? 'Đổi sản phẩm' : 'Hoàn tiền' }}</p>
                        <p><strong>Lý do:</strong> {{ $returnRequest->reason }}</p>
                        <ul class="admin-return-items">
                            @foreach ($returnRequest->items as $requestItem)
                                <li>{{ $requestItem->orderItem?->product_name }} × {{ $requestItem->quantity }} @if($requestItem->reason) · {{ $requestItem->reason }} @endif</li>
                            @endforeach
                        </ul>
                        @if ($returnRequest->evidence_image)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($returnRequest->evidence_image) }}" target="_blank" rel="noopener">Mở ảnh bằng chứng</a>
                        @endif
                        @if ($returnRequest->admin_note)<p class="admin-return-note"><strong>Phản hồi:</strong> {{ $returnRequest->admin_note }}</p>@endif
                        @if ($returnRequest->reject_reason)<p class="text-danger"><strong>Lý do từ chối:</strong> {{ $returnRequest->reject_reason }}</p>@endif
                        @if ($returnRequest->return_type === 'refund' && $returnRequest->status === 'completed' && $returnRequest->refund_amount !== null)
                            <p class="admin-refund-amount"><strong>Đã hoàn:</strong> {{ number_format((float) $returnRequest->refund_amount, 0, ',', '.') }}₫</p>
                        @endif

                        @if ($availableReturnStatuses)
                            <form action="{{ route('admin.returns.update', $returnRequest) }}" method="POST" class="admin-return-update-form" id="admin-order-return-form-{{ $returnRequest->id }}">
                                @csrf
                                @method('PATCH')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="admin-order-return-status-{{ $returnRequest->id }}">Bước tiếp theo</label>
                                        <select name="status" id="admin-order-return-status-{{ $returnRequest->id }}" class="form-select form-input" required>
                                            <option value="">Chọn trạng thái</option>
                                            @foreach ($availableReturnStatuses as $nextStatus)
                                                <option value="{{ $nextStatus }}">{{ $returnStatusLabels[$nextStatus] ?? $nextStatus }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="admin-order-return-refund-{{ $returnRequest->id }}">Số tiền hoàn (nếu hoàn tiền)</label>
                                        <input type="number" name="refund_amount" id="admin-order-return-refund-{{ $returnRequest->id }}" class="form-control form-input" min="0" step="0.01" value="{{ old('refund_amount') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="admin-order-return-reject-{{ $returnRequest->id }}">Lý do từ chối</label>
                                        <input type="text" name="reject_reason" id="admin-order-return-reject-{{ $returnRequest->id }}" class="form-control form-input" maxlength="5000">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="admin-order-return-note-{{ $returnRequest->id }}">Phản hồi khách hàng</label>
                                        <textarea name="admin_note" id="admin-order-return-note-{{ $returnRequest->id }}" class="form-control form-textarea" rows="2">{{ old('admin_note', $returnRequest->admin_note) }}</textarea>
                                    </div>
                                    <div class="col-12"><button type="submit" class="btn btn-primary btn-sm">Cập nhật yêu cầu</button></div>
                                </div>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class="text-muted mb-0">Đơn hàng chưa có yêu cầu đổi trả.</p>
                @endforelse
            </section>
        </div>

        <aside class="admin-order-side-column">
            <section class="admin-card admin-order-panel" id="admin-order-customer-panel">
                <div class="admin-order-panel-heading"><h2>Khách hàng</h2></div>
                <p><strong>{{ $order->recipient_name }}</strong></p>
                <p>{{ $order->user?->email }}</p>
                <p>{{ $order->phone }}</p>
                <p class="mb-0">{{ $order->address }}</p>
            </section>

            <section class="admin-card admin-order-panel" id="admin-order-payment-panel">
                <div class="admin-order-panel-heading"><h2>Thanh toán</h2></div>
                <p><strong>{{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : $order->payment_method }}</strong></p>
                <p>Trạng thái: {{ ['pending' => 'Đang chờ', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', 'refunded' => 'Đã hoàn'][$order->payment_status] ?? $order->payment_status }}</p>
                @if ($latestPayment)
                    <p>Giao dịch: {{ $latestPayment->transaction_code ?: '—' }}</p>
                    <p>Số tiền: {{ number_format((float) $latestPayment->amount, 0, ',', '.') }}₫</p>
                    @if ($latestPayment->paid_at)<p>Đã thanh toán lúc {{ $latestPayment->paid_at->format('d/m/Y H:i') }}</p>@endif
                    @if ($latestPayment->refunded_at)<p>Hoàn tiền lúc {{ $latestPayment->refunded_at->format('d/m/Y H:i') }}</p>@endif
                @endif
            </section>

            <section class="admin-card admin-order-panel" id="admin-order-status-panel">
                <div class="admin-order-panel-heading"><h2>Cập nhật trạng thái</h2></div>
                @if ($nextStatuses)
                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" id="admin-order-status-form">
                        @csrf
                        @method('PATCH')
                        <label for="admin-order-status-next" class="form-label">Chuyển sang</label>
                        <select name="status" id="admin-order-status-next" class="form-select form-input" required>
                            <option value="">Chọn trạng thái kế tiếp</option>
                            @foreach ($nextStatuses as $nextStatus)
                                <option value="{{ $nextStatus }}">{{ $nextStatusLabels[$nextStatus] ?? $statusLabels[$nextStatus] }}</option>
                            @endforeach
                        </select>
                        <label for="admin-order-status-reason" class="form-label mt-3">Ghi chú lịch sử (không bắt buộc)</label>
                        <textarea name="reason" id="admin-order-status-reason" class="form-control form-textarea" rows="2" maxlength="255"></textarea>
                        <button type="submit" class="btn btn-primary w-100 mt-3">Lưu trạng thái</button>
                    </form>
                @else
                    <p class="text-muted mb-0">Đơn hàng đang ở trạng thái kết thúc.</p>
                @endif
            </section>

            <section class="admin-card admin-order-panel" id="admin-order-tracking-panel">
                <div class="admin-order-panel-heading"><h2>Thông tin vận chuyển</h2></div>
                <form action="{{ route('admin.orders.tracking', $order) }}" method="POST" id="admin-order-tracking-form">
                    @csrf
                    @method('PATCH')
                    <label for="admin-order-carrier" class="form-label">Đơn vị vận chuyển</label>
                    <input name="shipping_carrier" id="admin-order-carrier" class="form-control form-input mb-3" maxlength="50" value="{{ old('shipping_carrier', $order->shipping_carrier) }}">
                    <label for="admin-order-tracking-code" class="form-label">Mã vận đơn</label>
                    <input name="tracking_code" id="admin-order-tracking-code" class="form-control form-input mb-3" maxlength="100" value="{{ old('tracking_code', $order->tracking_code) }}">
                    <label for="admin-order-estimated-date" class="form-label">Ngày giao dự kiến</label>
                    <input type="date" name="estimated_delivery_date" id="admin-order-estimated-date" class="form-control form-input" value="{{ old('estimated_delivery_date', $order->estimated_delivery_date?->format('Y-m-d')) }}">
                    <button type="submit" class="btn btn-outline w-100 mt-3" @disabled($order->status === 'cancelled')>Lưu vận chuyển</button>
                </form>
            </section>

            @if ($canRequestCancellation)
                <section class="admin-card admin-order-panel admin-order-cancel-panel" id="admin-order-cancel-panel">
                    <div class="admin-order-panel-heading"><h2>Hủy đơn hàng</h2></div>
                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" id="admin-order-cancel-form">
                        @csrf
                        <label for="admin-order-cancel-reason" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea name="reason" id="admin-order-cancel-reason" class="form-control form-textarea" rows="3" maxlength="255" required></textarea>
                        <button type="submit" class="btn btn-danger w-100 mt-3">Hủy qua dịch vụ xử lý</button>
                    </form>
                    <small class="text-muted d-block mt-2">Tồn kho do CancellationService quản lý.</small>
                </section>
            @endif
        </aside>
    </div>
</section>
@endsection
