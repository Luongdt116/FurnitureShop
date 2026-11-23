@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'helper' => null,
    'id' => null,
])

@php
    $inputId = $id ?? 'form-' . str_replace(['[', ']'], ['-', ''], $name);
    $hasError = $errors->has($name);
@endphp

<div class="form-group mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="required text-danger">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" 
           name="{{ $name }}" 
           id="{{ $inputId }}" 
           value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           {{ $required ? 'required' : '' }}
           {{ $attributes->merge(['class' => 'form-control form-input' . ($hasError ? ' is-invalid border-danger' : '')]) }}>

    @if ($hasError)
        <div class="invalid-feedback d-block text-danger small mt-1">
            {{ $errors->first($name) }}
        </div>
    @elseif ($helper)
        <div class="form-helper text-muted small mt-1">{{ $helper }}</div>
    @endif
</div>
