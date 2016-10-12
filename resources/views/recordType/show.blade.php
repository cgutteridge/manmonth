@extends('page')

@section('title')
    View Record Type: @title($recordType)
@endsection
@section( 'content' )
    <div class="panel panel-info mm-record-block">
        <div class="panel-heading">
            <a href="@url($recordType,'edit')" class="pull-right" title="edit"><span
                        class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
            &nbsp;
        </div>
        <table class="table mm-table">
            @include('dataTable',['data'=>$recordType->data])
        </table>
    </div>

    @if( count($recordType->forwardLinkTypes) )
        <h3>Links from @title($recordType)</h3>
        <ul>
            @foreach( $recordType->forwardLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->range_min,"max"=>$linkType->range_max])
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
                    @include("cardinality",["min"=>$linkType->domain_min,"max"=>$linkType->domain_max])
                    <a href="@url($linkType)">@title($linkType,"inverse")</a> relation linking
                    from @link($linkType->domain)
                </li>
            @endforeach
        </ul>
    @endif

    @if( count($recordType->reportTypes ))
        <h3>Report Types</h3>
        <ul>
            @foreach( $recordType->reportTypes as $reportType )
                <li>
                    @link($reportType) (runs
                    on @link($reportType->baseRecordType()), {{$reportType->rules()->count()}} rule(s))
                </li>
            @endforeach
            <li>TODO: Create new record type</li>
        </ul>
    @endif

@endsection


