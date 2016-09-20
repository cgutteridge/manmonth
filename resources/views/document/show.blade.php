@extends('page')

@section('title', $document->name )

@section( 'content')
    @include( 'dataTable', [ "data"=>[
        "name"=>$document->name,
        "created_at"=>$document->created_at,
        "updated_at"=>$document->updated_at,
    ]])
    <ul>
    @foreach( $document->revisions as $revision )
        <li>
            <a href="/revisions/{{$revision->id}}">#{{$revision->id}} ({{$revision->status}}) {{$revision->created_at}}</a>
        </li>
    @endforeach
    </ul>
@endsection