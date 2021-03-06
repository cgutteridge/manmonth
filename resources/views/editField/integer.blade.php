<tr>
    <th>
        @include('editField.label')
    </th>
    <td>
        <input
                type="number"
                class="form-control"
                id="{{ $idPrefix }}"
                name="{{ $idPrefix }}"
                @if(array_key_exists("min",$field->data))
                min="{{$field->data["min"]}}"
                @endif
                @if(array_key_exists("max",$field->data))
                max="{{$field->data["max"]}}"
                @endif
                step="1"
                @if($field->description()!=null)
                aria-describedby="{{ $idPrefix }}_help"
                @endif
                placeholder="{{$placeholder}}"
                value="{{$value}}">
    </td>
</tr>
