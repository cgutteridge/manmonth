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

    <div class="mm-filtered">
        @foreach( $records as $code=>$recordInList )
            <div class="mm-filtered-item" data-mm-filter-code="{{$code}}"
                 data-mm-filter-link="@url($recordInList['record'])">
                @include("record.miniblock",$recordInList)
            </div>
        @endforeach
    </div>
@endsection


