@extends('page')

@section('title','List Documents')

@section( 'content')
    <ul>
        @foreach( $list as $document )
            @can( 'view-published-latest', $document)
                <li>
                    @link( $document ), Created @datetime($document->created_at).
                </li>
            @endcan
        @endforeach
    </ul>
@endsection
