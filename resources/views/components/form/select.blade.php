@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'required' => false,
    'placeholder' => null,
    'id' => null,
])

@php
    $selectId = $id ?? 'form-' . str_replace(['[', ']'], ['-', ''], $name);
    $hasError = $errors->has($name);
    $currentVal = old($name, $selected);
@endphp

<div class="form-group mb-3">
    @if ($label)
        <label for="{{ $selectId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="required text-danger">*</span>
            @endif
        </label>
    @endif

    <select name="{{ $name }}" 
            id="{{ $selectId }}" 
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'form-select form-input' . ($hasError ? ' is-invalid border-danger' : '')]) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if (!empty($options))
            @foreach ($options as $val => $text)
                <option value="{{ $val }}" {{ (string)$currentVal === (string)$val ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @if ($hasError)
        <div class="invalid-feedback d-block text-danger small mt-1">
            {{ $errors->first($name) }}
        </div>
    @endif
</div>
