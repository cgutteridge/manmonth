@extends('page')

@section('title')
    @title($reportType)
@endsection
@section( 'content')
    <div class="panel panel-primary mm-report-panel">
        <div class="panel-heading">
            <b>Report</b>
        </div>

        <div class="panel-body ">

            <div class='mm-report-wrapper'>

                <nav class="navbar">
                    <div class="container-fluid">

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse" >
                            <ul class="nav navbar-nav">
                                <p class="navbar-text">View: </p>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false">
                                        <span class="mm-report-current-view">Graph with absolute scale</span>
                                        <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" data-mm-report-view="absolute"><span><span class="glyphicon glyphicon-ok"></span> Graph with absolute scale</a>
                                        </li>
                                        <li><a href="#" data-mm-report-view="targets">Graph scaled by targets</a></li>
                                        <li><a href="#" data-mm-report-view="totals">Graph scaled by totals</a></li>
                                        <li><a href="#" data-mm-report-view="breakdown">Textual breakdown</a></li>
                                        <li><a href="#" data-mm-report-view="none">Tabular data only</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>

                <table class='mm-report'>
                    <thead>
                    <tr>
                        <th class="mm_report_header_icon"></th>
                        <th class="mm_report_header_icon"></th>
                        @foreach( $reportData['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                            <th class="mm-report-header-data">{{ $colName }}</th>
                        @endforeach
                        @if( count( $reportData["categories"])>1)
                            @foreach( $reportData["categories"] as $category=>$opts )
                                <th class="mm-report-header-data">{{ array_key_exists('label',$opts)?$opts['label']:$category }}</th>
                            @endforeach
                        @endif
                        <th class="mm-report-header-data">Total</th>
                        <th class="mm-report-header-data">Target</th>
                        <th class="mm_report_header_graph"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $reportData['rows'] as $row )
                        @include( 'reportType.recordRow', $row )
                    @endforeach
                    </tbody>
                    <tfoot>
                    @if( count($reportData['means']) )
                        <tr>
                            <th colspan="2">Mean:</th>
                            @foreach( $reportData['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                                <td class="mm-record-report-data">
                                    @if( array_key_exists($colName,$reportData['means']) )
                                        {{ sprintf("%2.2f",$reportData['means'][$colName]) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                    @if( count($reportData['totals']) )
                        <tr>
                            <th colspan="2">Total:</th>
                            @foreach( $reportData['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                                <td class="mm-record-report-data">
                                    @if( array_key_exists($colName,$reportData['totals']) )
                                        {{ $reportData['totals'][$colName] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading"><b>Rules</b></div>
        <div class="panel-body">
            @foreach( $reportType->rules() as $rule )
                <div class="panel panel-info mm-record-block">
                    <div class="panel-heading ">
                        <b>Rule #{{ $rule->rank+1 }}</b>
                    </div>
                    <table class="table">
                        @include( 'dataTable', ['data'=>$rule->data ])
                    </table>
                </div>
            @endforeach
        </div>
    </div>

    @if(false)
        <div class="panel panel-primary">
            <div class="panel-heading"><b>Log</b></div>

            <div class="panel-body">
                <p>This is a list of every action triggered on every record.</p>
                @foreach( $reportType->baseRecordType()->records() as $record)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            @include( 'dataTable', ['data'=>$record->data ])
                        </div>
                        <ol class="list-group">
                            @foreach( $report->recordReports()[ $record->sid ]->getLog() as $log )
                                <li class="list-group-item">
                                    @include( "dataTable", [ "data"=>$log] )
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
