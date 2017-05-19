<select name="{{$idPrefix}}" id="{{$idPrefix}}">
    <option value="">-- select --</option>
    @foreach( $recordType->records()->sortBy( function($item,$key){ return (new \App\Http\TitleMaker())->title( $item ); }) as $item)
        <option value="{{$item->sid}}"
                @if(isset($record) && $record->sid==$item->sid)
                selected="selected"
                @endif
        >@title($item)</option>
        {{$item}}
    @endforeach
</select>
