@props([
    'variant' => 'primary', // primary, gold, success, warning, danger, vip
])

@php
    $badgeClass = match($variant) {
        'gold' => 'badge-gold',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'vip' => 'badge-vip',
        default => 'badge-primary',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$badgeClass}"]) }}>
    {{ $slot }}
</span>
