@if( !isset( $max ) )
    {{$min}}..n
@elseif($min==$max)
    {{$min}}
@else
    {{$min}}..{{$max}}
@endif