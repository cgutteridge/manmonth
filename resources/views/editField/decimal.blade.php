<label for="{{$idPrefix}}_{{$field->data["name"]}}">{{ $field->title() }}</label>
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
        placeholder="Enter {{$field->title()}}" value="{{$value}}">
