@extends('page')

@section('title','Available documents')

@section( 'content')
    @if($documents->count() > 0 )
        <ul>
            @foreach( $documents as $document )
                <li>
                    @link( $document ), Created @datetime($document->created_at).
                </li>
            @endforeach
        </ul>
    @else
        <p>You do not have the permissions required to see any documents on this service.</p>
    @endif
@endsection
