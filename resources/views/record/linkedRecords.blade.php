DEPRECATE?


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
