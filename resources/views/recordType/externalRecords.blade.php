@extends('page')

@section('title')
    View Records of Type @title($recordType)
@endsection

@section( 'content' )
    @can('create',$recordType)
        <p>
            <a type="button" class="btn btn-primary" href="@url($recordType,'create-record')">
                New @title($recordType)
            </a>
        </p>
    @endcan
    @foreach( $records as $record )
        @include("record.block",$record)
    @endforeach
@endsection


