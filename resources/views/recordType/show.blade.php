@extends('page')

@section('title')
    View Record Type: @title($recordType)
@endsection
@section( 'content' )
    <h3>Schema</h3>
    @can('edit',$recordType)
        <p>
            <a type="button" class="btn btn-primary" href="@url($recordType,'edit')">Edit Schema</a>
        </p>
    @endcan
    <table class="table mm-datatable">
        @include('dataTable',['data'=>$recordType->data])
    </table>

    @if( count($recordType->forwardLinkTypes) )
        <h3>Links from @title($recordType)</h3>
        <ul>
            @foreach( $recordType->forwardLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->domain_min,"max"=>$linkType->domain_max])
                    @link($linkType) relation linking to @link($linkType->range)
                </li>
            @endforeach
        </ul>
    @endif

    @if( count($recordType->backLinkTypes) )
        <h3>Links to @title($recordType)</h3>
        <ul>
            @foreach( $recordType->backLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->range_min,"max"=>$linkType->range_max])
                    <a href="@url($linkType)">@title($linkType,"inverse")</a> relation linking
                    from @link($linkType->domain)
                </li>
            @endforeach
        </ul>
    @endif

    <h3>Report Types</h3>
    @if( count($recordType->reportTypes ))
        <ul>
            @foreach( $recordType->reportTypes as $reportType )
                <li>
                    @link($reportType) (runs
                    on @link($reportType->baseRecordType()), {{$reportType->rules()->count()}} rule(s))
                </li>
            @endforeach
        </ul>
    @else
        None.
    @endif

@endsection


