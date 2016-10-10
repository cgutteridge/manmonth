<label for="{{$idPrefix}}">@title($field)</label>
<input
        type="text"
        class="form-control"
        id="{{ $idPrefix }}"
        name="{{ $idPrefix }}"
        @if($field->description()!=null)
            aria-describedby="{{ $idPrefix }}_help"
        @endif
        placeholder="Enter @title($field)" value="{{$value}}">