<select name="{{$idPrefix}}" id="{{$idPrefix}}">
    @if(!isset($record))
        <option value="">-- select --</option>
    @endif
    @foreach( $recordType->records as $item)
        <option value="{{$item->sid}}"
                @if(isset($record) && $record->sid==$item->sid)
                selected="selected"
                @endif
        >@title($item)</option>
        {{$item}}
    @endforeach
</select>
