@props([
    'name',
    'label' => null,
    'rows' => 4,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'helper' => null,
    'id' => null,
])

@php
    $textareaId = $id ?? 'form-' . str_replace(['[', ']'], ['-', ''], $name);
    $hasError = $errors->has($name);
@endphp

<div class="form-group mb-3">
    @if ($label)
        <label for="{{ $textareaId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="required text-danger">*</span>
            @endif
        </label>
    @endif

    <textarea name="{{ $name }}" 
              id="{{ $textareaId }}" 
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              {{ $required ? 'required' : '' }}
              {{ $attributes->merge(['class' => 'form-control form-textarea' . ($hasError ? ' is-invalid border-danger' : '')]) }}>{{ old($name, $value) }}</textarea>

    @if ($hasError)
        <div class="invalid-feedback d-block text-danger small mt-1">
            {{ $errors->first($name) }}
        </div>
    @elseif ($helper)
        <div class="form-helper text-muted small mt-1">{{ $helper }}</div>
    @endif
</div>
