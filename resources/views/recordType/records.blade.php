@extends('page')

@section('title')
    View Records of Type @title($recordType)
@endsection

@section( 'content' )
    <p>
        <a type="button" class="btn btn-primary" href="@url($recordType,'create-record')">
            New @title($recordType)
        </a>
    </p>
    @foreach( $records as $record )
        @include("record.block",$record)
    @endforeach
@endsection


