@extends('page')

@section('title','List Documents')

@section( 'content')
<ul>
    @foreach( $list as $document )
    <li>
        @link( $document ), Created @datetime($document->created_at).
    </li>
    @endforeach
</ul>
@endsection
