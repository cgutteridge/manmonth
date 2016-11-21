@extends('page')

@section('title')
    List all '@title($linkType,"long")' links
@endsection

@section( 'content' )
    <p>Connects @link($linkType->domain) to @link($linkType->range)</p>
    <ul>
        @foreach( $linkType->links as $link )
            <li>@link($link->subjectRecord) to @link($link->objectRecord)</li>
        @endforeach
    </ul>
@endsection


