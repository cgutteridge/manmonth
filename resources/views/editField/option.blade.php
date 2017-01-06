<tr>
    <th>
        @include('editField.label')
    </th>
    <td>
        <select
                id="{{ $idPrefix }}"
                name="{{ $idPrefix }}"
                class="form-control"
                @if($field->description()!=null)
                aria-describedby="{{ $idPrefix }}_help"
                @endif
        >
            @foreach($field->optionsWithLabels() as $code=>$label)
                <option
                        value="{{$code}}"
                        @if(isset($value) && $value==$code)
                        selected="selected"
                        @endif
                        @if(!isset($value) && isset($field->data["default"]) && $field->data["default"]==$code)
                        selected="selected"
                        @endif
                >{{$label}}</option>
            @endforeach
        </select>
    </td>
</tr>
