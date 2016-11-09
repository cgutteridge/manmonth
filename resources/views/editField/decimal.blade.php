<tr>
    <th>
        <label for="{{$idPrefix}}_{{$field->data["name"]}}">@title($field)</label>:
    </th>
    <td>
        <input
                type="number"
                class="form-control"
                id="{{ $idPrefix }}"
                name="{{ $idPrefix }}"
                step="any"
                @if(array_key_exists("min",$field->data))
                min="{{$field->data["min"]}}"
                @endif
                @if(array_key_exists("max",$field->data))
                max="{{$field->data["max"]}}"
                @endif
                @if($field->description()!=null)
                aria-describedby="{{ $idPrefix }}_help"
                @endif
                placeholder="Enter @title($field)" value="{{$value}}">
    </td>
</tr>