@extends('page')

@section('title')
    Bulk Import Records of Type @title($recordType)
@endsection

@section( 'content' )
    <p>You are about to create <strong>{{$toImportCount}}</strong> record{{($toImportCount==1?"":"s")}}.</p>
    <p>Filters:</p>
    <ul>
        @foreach( $filters as $column=>$value )
            <li>{{$column}} = <strong>{{$value}}</strong></li>
        @endforeach
    </ul>
    @if( $wontImportCount )
        <p>Note: <strong>{{$wontImportCount}}</strong> record{{($wontImportCount==1?"":"s")}} already
            exist{{($wontImportCount==1?"s":"")}} and won't be created.</p>
    @endif

    <form method="post" action="{{$importUrl}}">
        @include('form.commonBits', ["returnTo"=>$cancelUrl])
        @foreach( $importUrlParams as $name=>$value)
            <input type="hidden" name="{{$name}}" value="{{$value}}"/>
        @endforeach
        <input class="btn btn-primary" type="submit"
               value="Create {{$toImportCount}} record{{($toImportCount==1?"":"s")}}"/>
        <a href="{{$cancelUrl}}" class="btn btn-primary">Cancel</a>

    </form>

@endsection


