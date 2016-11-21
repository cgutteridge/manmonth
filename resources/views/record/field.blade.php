<select name="{{$idPrefix}}" id="{{$idPrefix}}">
    <option value="">-- select --</option>
    @foreach( $recordType->records as $item)
        <option value="{{$item->sid}}"
                @if(isset($record) && $record->sid==$item->sid)
                selected="selected"
                @endif
        >@title($item)</option>
        {{$item}}
    @endforeach
</select>
