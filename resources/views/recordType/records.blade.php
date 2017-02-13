@extends('page')

@section('title')
    View Records of Type @title($recordType)
@endsection

@section( 'content' )
    <p>
        @can('create',$recordType)
            <a type="button" class="btn btn-primary" href="@url($recordType,'create-record')">
                New @title($recordType)
            </a>

            @if( $recordType->isLinkedToExternalData() )
                <a type="button" class="btn btn-primary" href="@url($recordType,'external-records')">
                    New @title($recordType) from External Data
                </a>
            @endif
        @endcan
        <a type="button" class="btn btn-primary" href="@url($recordType)">
            Schema
        </a>
    </p>

    @foreach( $records as $record )
        @include("record.block",$record)
    @endforeach
@endsection


