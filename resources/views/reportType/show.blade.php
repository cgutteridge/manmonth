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
                        <div class="collapse navbar-collapse">
                            <div class="nav navbar-nav">
                                <a type="button" class="btn btn-primary"
                                   href="@url($reportType,'export/summary/csv')">
                                    <span class="glyphicon glyphicon-th-list"></span>
                                    Export summary (CSV)
                                </a>
                                <a type="button" class="btn btn-primary"
                                   href="@url($reportType,'export/full/csv')">
                                    <span class="glyphicon glyphicon-th-list"></span>
                                    Export full (CSV)
                                </a>
                                <p class="navbar-text">View: </p>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false">
                                        <span class="mm-report-current-view">Graph with absolute scale</span>
                                        <span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" data-mm-report-view="absolute"><span
                                                            class="glyphicon glyphicon-ok"></span> Graph with absolute scale</a>
                                        </li>
                                        <li><a href="#" data-mm-report-view="targets">Graph scaled by targets</a></li>
                                        <li><a href="#" data-mm-report-view="totals">Graph scaled by totals</a></li>
                                        <li><a href="#" data-mm-report-view="breakdown">Textual breakdown</a></li>
                                        <li><a href="#" data-mm-report-view="none">Tabular data only</a></li>
                                    </ul>
                                </li>
                            </div>
                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>

                <!-- hacky. There should be a nicer way to add tablesorter. TODO -->
                <script type="text/javascript" src="/tablesorter/jquery.tablesorter.js"></script>
                <script type="text/javascript"> $(document).ready(function () {
                        $("#mm1").tablesorter();
                        $("#mm1 thead th").css('cursor', 'pointer');
                    }); </script>
                <table class='mm-report' id='mm1'>
                    <thead>
                    <tr>
                        <th class="mm_report_header_icon"></th>
                        <th class="mm_report_header_icon"></th>
                        @foreach( $reportData['columns'] as $colName )
                            <th class="mm-report-header-data">{{ $colName }}</th>
                        @endforeach
                        @if( count( $reportData["categories"])>1)
                            @foreach( $reportData["categories"] as $category=>$opts )
                                @if( !array_key_exists('show_column',$opts) || $opts['show_column'] )
                                    <th class="mm-report-header-data">{{ array_key_exists('label',$opts)?$opts['label']:$category }}</th>
                                @endif
                            @endforeach
                        @endif
                        <th class="mm-report-header-data">Total</th>
                        <th class="mm-report-header-data">Target</th>
                        <th class="mm-report-header-data">Ratio</th>
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
                            @foreach( $reportData['columns'] as $colName=>$colValue)
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
                            @foreach( $reportData['columns'] as $colName=>$colValue)
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
        <div class="panel-heading"><b>Reporting Rules</b></div>
        <div class="panel-body">
            @foreach( $rulesSections as $section )
                <h3>{{$section['label']}}</h3>
                @foreach( $rulesData as $ruleData )
                    @if( $ruleData['action_type']== $section['action_type'])
                        <div class="panel panel-default">
                            <div class="panel-heading"><b>{{$ruleData['action']}}</b> - {{ $ruleData['title'] }}
                                <small>(Rule #{{ $ruleData['number'] }})</small>
                            </div>
                            <div class="panel-body">
                                <div>
                                    <strong>Operates on:</strong>
                                    @foreach( $ruleData['route'] as $rItem)
                                        @if( $rItem['type']=='recordType')
                                            <div class="mm-lozenge" title="{{$rItem['codename']}}">
                                                <div style="font-weight: normal;font-size:60%">{{$rItem['codename']}}</div>
                                                <div style="font-weight: normal;">{{$rItem['title']}}</div>
                                            </div>
                                        @endif
                                        @if( $rItem['type']=='link')
                                            <div style="padding-bottom: 0px; display:inline-block; text-align:center; vertical-align: middle; font-size:100%;"
                                                 title="{{$rItem['title']}}">
                                                &rarr;
                                                {{$rItem['title']}}
                                                &rarr;
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @if( !empty($ruleData['trigger']))
                                    <div><strong>Triggered if:</strong> <span
                                                class="mm-code">{{$ruleData['trigger']}}</span></div>
                                @endif
                                @foreach( $ruleData['params'] as $key=>$value)
                                    <div><strong>{{$key}}:</strong> <span class="mm-code">{{$value}}</span></div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

@endsection
