<span class="mm-cardinality">
@if( !isset( $max ) )
        {{$min}}..*
@elseif($min==$max)
    {{$min}}
@else
    {{$min}}..{{$max}}
@endif
</span>