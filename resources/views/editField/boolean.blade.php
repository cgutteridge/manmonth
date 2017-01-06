<tr>

    <th>
        @include('editField.label')
    </th>
    <td>
        <input
                type="hidden"
                id="{{ $idPrefix }}_exists"
                name="{{ $idPrefix }}_exists"
                value="1"
        >
        <input
                type="checkbox"
                class="mm-form-checkbox-input-input"
                id="{{ $idPrefix }}"
                name="{{ $idPrefix }}"
                value="1"
                @if( $value )
                checked="checked"
                @endif
                @if($field->description()!=null)
                aria-describedby="{{ $idPrefix }}_help"
                @endif
        >
    </td>
</tr>
