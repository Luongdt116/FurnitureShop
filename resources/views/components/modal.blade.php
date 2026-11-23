@props([
    'id',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
])

@php
    $maxWidth = match($size) {
        'sm' => '440px',
        'lg' => '720px',
        'xl' => '900px',
        default => '580px',
    };
@endphp

<div id="{{ $id }}" class="modal-overlay" {{ $attributes }}>
    <div class="modal-box" style="max-width: {{ $maxWidth }};">
        <div class="modal-header">
            <div class="modal-title">{{ $title }}</div>
            <button type="button" class="modal-close" onclick="closeAuraModal('{{ $id }}')" aria-label="Đóng modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            {{ $slot }}
        </div>
        @if(isset($footer))
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
