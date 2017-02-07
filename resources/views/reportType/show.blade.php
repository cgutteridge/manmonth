@extends('page')

@section('title')
    @title($reportType)
@endsection
@section( 'content')
    <div class="panel panel-primary">
        <div class="panel-heading">
            <b>Report</b>
        </div>

        <div class="panel-body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                @foreach( $reportData["views"] as $viewId=>$view)
                    <li role="presentation"
                        class="{{ (isset($view['first'])&&$view['first'])?"active":""}}"
                    ><a href="#{{$viewId}}"
                        aria-controls="{{$viewId}}"
                        role="tab" data-toggle="tab">{{ $view["tabTitle"] }}</a>
                @endforeach
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                @foreach( $reportData["views"] as $viewId=>$view)
                    <div role="tabpanel"
                         class="tab-pane{{ (isset($view['first'])&&$view['first'])?"
                             active":"" }}" id="{{$viewId}}">
                        <h3>{{$view["title"]}}</h3>
                        <div class='mm_report_wrapper'>
                            <table class='mm_report'>
                                <thead>
                                <tr>
                                    <th class="mm_report_header_icon"></th>
                                    <th class="mm_report_header_icon"></th>
                                    @foreach( $view['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                                        <th class="mm_report_header_data">{{ $colName }}</th>
                                    @endforeach
                                    @if( count( $reportData["categories"])>1)
                                        @foreach( $reportData["categories"] as $category=>$opts )
                                            <th class="mm_report_header_data">{{ array_key_exists('label',$opts)?$opts['label']:$category }}</th>
                                        @endforeach
                                    @endif
                                    <th class="mm_report_header_data">Total</th>
                                    <th class="mm_report_header_data">Target</th>
                                    <th class="mm_report_header_graph"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach( $view['rows'] as $row )
                                    @include( 'reportType.recordRow', $row )
                                @endforeach
                                </tbody>
                                <tfoot>
                                @if( count($view['means']) )
                                    <tr>
                                        <th colspan="2">Mean:</th>
                                        @foreach( $view['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                                            <td class="mm_record_report_data">
                                                @if( array_key_exists($colName,$view['means']) )
                                                    {{ sprintf("%2.2f",$view['means'][$colName]) }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                                @if( count($view['totals']) )
                                    <tr>
                                        <th colspan="2">Total:</th>
                                        @foreach( $view['rows'][0]['recordReport']->getColumns() as $colName=>$colValue)
                                            <td class="mm_record_report_data">
                                                @if( array_key_exists($colName,$view['totals']) )
                                                    {{ $view['totals'][$colName] }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
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
                @foreach( $reportType->baseRecordType()->records as $record)
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
