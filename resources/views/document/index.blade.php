@extends('page')

@section('title','List Documents')

@section( 'content')
<ul>
    @foreach( $list as $document )
    <li>
        @can( 'view-published', $document)
            YES YOU CAN CAN CAN
        @else
            NOPE
        @endcan
        @link( $document ), Created @datetime($document->created_at).
    </li>
    @endforeach
</ul>
@endsection
