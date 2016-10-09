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
                    @link($linkType) linking to @link($linkType->range)
                </li>
            @endforeach
        </ul>
        TODO: make these link and show nice cardinality
    @endif

    @if( count($recordType->backLinkTypes) )
        <h3>Links to @title($recordType)</h3>
        <ul>
            @foreach( $recordType->backLinkTypes as $linkType )
                <li>
                    <a href="@url($linkType)}}">{{$linkType->inverseTitle()}}</a> linking from @link($linkType->domain)
                </li>
            @endforeach
        </ul>
        TODO: make these link and show nice cardinality
    @endif

    @if( count($recordType->reportTypes ))
        <h3>Report Types</h3>
        <ul>
            @foreach( $recordType->reportTypes as $reportType )
                <li>
                    @link($reportType) (runs
                    on @link($reportType->baseRecordType()), {{$reportType->rules()->count()}} rule(s))</a>
                </li>
            @endforeach
            <li>TODO: Create new record type</li>
        </ul>
    @endif

@endsection


