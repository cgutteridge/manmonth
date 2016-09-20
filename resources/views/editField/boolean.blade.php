<label>
    <input
            type="hidden"
            id="{{ $idPrefix }}_exists"
            name="{{ $idPrefix }}_exists"
            value="1"
    >
    <input
            type="checkbox"
            class="form-check-input"
            id="{{ $idPrefix }}"
            name="{{ $idPrefix }}"
            value="1"
            @if( $value )
            checked="checked"
            @endif
            @if($field->description()!=null)
            aria-describedby="{{ $idPrefix }}_help"
            @endif
    > {{ $field->title() }}
</label>
