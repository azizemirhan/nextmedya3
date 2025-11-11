{{-- resources/views/admin/pages/partials/_input_element.blade.php --}}
@php
    $dataAttrs = 'data-name="' . $field['name'] . '"' . ($lang ? ' data-lang="' . $lang . '"' : '');
@endphp

@if($field['type'] === 'textarea')
    <div class="quill-editor-wrapper">
        <div class="quill-editor">{!! $value ?? '' !!}</div>
        <input type="hidden" {!! $dataAttrs !!} value="{{ $value ?? '' }}">
    </div>
@elseif($field['type'] === 'checkbox')
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" {!! $dataAttrs !!} value="1"
                @checked(($value ?? false) == true || $value == 1 || $value === 'true' || $value === '1')>
        <label class="form-check-label">{{ $field['label'] ?? 'Aktif' }}</label>
    </div>
@elseif($field['type'] === 'file')
    <input type="file" class="form-control" {!! $dataAttrs !!}>
    @if(!empty($value))
        <div class="mt-2">
            <img src="{{ asset($value) }}" height="50" alt="Mevcut Resim">
            <small class="d-block text-muted">{{ $value }}</small>
        </div>
    @endif
@elseif($field['type'] === 'select' && isset($field['options']))
    <select class="form-select" {!! $dataAttrs !!}>
        @foreach($field['options'] as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(($value ?? '') == $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
@else
    <input type="{{ $field['type'] }}" class="form-control" {!! $dataAttrs !!} value="{{ $value ?? '' }}">
@endif