@extends('page')

@section('title')
    @title($documentRevision->document) rev #{{$documentRevision->id}} ({{$documentRevision->status}})
@endsection

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 style="margin: 0">Revision Data</h2>
        </div>
        <div class="row panel-body">
            <div class="col-md-6">
                <h3>Metadata</h3>
                <table class="table">
                    @include( 'dataTable', [ "data"=>[
        "status"=>$documentRevision->status,
        "created_at"=>$documentRevision->created_at,
        "updated_at"=>$documentRevision->updated_at,
    ]])
                </table>

            </div>
            <div class="col-md-6">
                <h3>Records</h3>
                <ul>
                    @foreach( $documentRevision->recordTypes as $recordType )
                        <li>
                            <a href="@url( $recordType, 'records' )">@title($recordType)</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-6">
                <h3>Saved Reports</h3>
                <p>Coming "soon".</p>
            </div>
            <div class="col-md-6">
                <h3>Report Types</h3>
                <ul>
                    @foreach( $documentRevision->reportTypes as $reportType )
                        <li>
                            <a href="@url($reportType)">@title($reportType)
                                #{{$reportType->sid}}
                                (runs
                                on @title($reportType->baseRecordType()), {{$reportType->rules()->count()}}
                                rule(s))</a>
                        </li>
                    @endforeach
                    <li>TODO: Create new report type</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 style="margin:0">Revision Schema</h2>
        </div>
        <div class="row panel-body">
            <div class="col-md-6">
                <h3>Record Types</h3>
                <ul>
                    @foreach( $documentRevision->recordTypes as $recordType )
                        <li>
                            @link($recordType)
                        </li>
                    @endforeach
                    <li>TODO: Create new record type</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h3>Link Types</h3>
                <ul>
                    @foreach( $documentRevision->linkTypes as $linkType )
                        <li>
                            @link($linkType)
                        </li>
                    @endforeach
                    <li>TODO: Create new record type</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
