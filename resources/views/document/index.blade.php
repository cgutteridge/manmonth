@inject('linkMaker','App\Http\LinkMaker')
@extends('page')

@section('title','List Documents')

@section( 'content')
<ul>
    @foreach( $list as $document )
    <li>
        <a href="{{ $linkMaker->link( $document ) }}">{{ $document->name }}</a>, Created {{ $document->created_at }}.
    </li>
    @endforeach
</ul>
@endsection
