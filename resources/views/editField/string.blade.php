<tr>
    <th>
        <label for="{{$idPrefix}}">@title($field)</label>:
    </th>
    <td>
        <input
                type="text"
                class="form-control"
                id="{{ $idPrefix }}"
                name="{{ $idPrefix }}"
                @if($field->description()!=null)
                aria-describedby="{{ $idPrefix }}_help"
                @endif
                placeholder="Enter @title($field)" value="{{$value}}"/>
    </td>
</tr>
