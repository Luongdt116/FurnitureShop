@props([
    'variant' => 'primary', // primary, accent, outline, ghost, danger
    'size' => 'md',        // sm, md, lg
    'type' => 'button',    // button, submit, reset
    'href' => null,
    'icon' => null,
])

@php
    $classes = 'btn';
    
    // Variant classes
    $variantClass = match($variant) {
        'accent' => 'btn-accent',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        default => 'btn-primary',
    };
    $classes .= ' ' . $variantClass;

    // Size classes
    if ($size === 'sm') {
        $classes .= ' btn-sm';
    } elseif ($size === 'lg') {
        $classes .= ' btn-lg';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
