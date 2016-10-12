<div>Cardinality:
    @include( "cardinality",["min"=>$min, "max"=>$max ])
    with
    {{count($records)}} linked record{{count($records)==1?"":"s"}}.
    @if( isset($max) && count($records) > $max )
        [Too many links]
    @endif
    @if( count($records) < $max )
        [Too few links]
    @endif
</div>

@foreach( $records as $record )
    @if( $followLink == 'all' || (isset($max) && $max==1 ))
        @if( !array_key_exists($record->id,$seen))
            @include( 'record.block',[
                'record'=>$record,
                'followLink'=>'single',
                'editParams'=>$editParams,
                'seen'=>$seen ])
        @endif
    @endif
@endforeach

