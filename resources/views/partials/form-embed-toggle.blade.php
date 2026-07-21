<div{!! $componentIdAttr !!} class="vb-form-embed-field vb-form-embed-field--toggle">
    <label class="vb-form-embed-toggle" for="{{ $fieldName }}">
        <input
            id="{{ $fieldName }}"
            type="checkbox"
            role="switch"
            name="{{ $fieldName }}"
            value="1"
            @checked((bool) ($props['default_checked'] ?? false))
            @required($isRequired)
            @disabled($isBuilder)
        />
        <span class="vb-form-embed-toggle-track"><span class="vb-form-embed-toggle-thumb"></span></span>
        <div class="vb-form-label-content vb-form-embed-label">{!! $label !== '' ? $label : e(__('Toggle')) !!}</div>
    </label>

    @if($helperText !== '')
        <p class="vb-form-embed-helper">{{ $helperText }}</p>
    @endif
</div>
