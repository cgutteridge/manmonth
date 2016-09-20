@extends('page')

@section('title','List Documents')

@section( 'content')
<ul>
    @foreach( $list as $item )
    <li>
        <a href="/documents/{{ $item->id }}">{{ $item->name }}</a>, Created {{ $item->created_at }}.
    </li>
    @endforeach
</ul>
@endsection