@extends('page')

@section('title')
    List Links of Type @link($linkType)
@endsection

@section( 'content' )
    <p>@link($linkType->domain) to @link($linkType->range)</p>
    <p>TODO: Create new link</p>
    <ul>
        @foreach( $linkType->links as $link )
            <li>@link($link->subjectRecord) to @link($link->objectRecord)</li>
        @endforeach
    </ul>
@endsection


