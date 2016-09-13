@extends('page')

@section('title','Report Type #'.$reportType->sid)

@section( 'content')




    @foreach( $report->loadingTypes() as $loadingType )
        <div class="panel panel-primary">
            <div class="panel-heading">
                <b>Report: {{$loadingType}}</b>
            </div>
            <div id='p' class="panel-body">Scale:
                <div id='b' class="btn-group" role="group" aria-label="...">
                    <button id='b1' type="button" class="btn btn-primary">Absolute</button>
                    <button id='b2' type="button" class="btn btn-default">Relative to Target</button>
                    <button id='b3' type="button" class="btn btn-default">Relative to Assigned Load</button>
                </div>
                <div id="p1" class="panel">
                    <h3>Absolute scale</h3>
                    @foreach( $reportType->baseRecordType()->records as $record)
                        @include( 'reportTypeRecordRow', [
                            "showFree"=>true,
                            "showTarget"=>true,
                            "record"=>$record,
                            "recordReport"=>$report->recordReports()[ $record->sid ],
                            "scale" => 1/max( $report->maxLoading($loadingType), $report->maxTarget($loadingType) ),
                            "target" => $report->recordReports()[ $record->sid ]->getLoadingTarget( $loadingType ),
                            "total" => $report->recordReports()[ $record->sid ]->getLoadingTotal( $loadingType )
                        ])
                    @endforeach
                </div>
                <div id="p2" class="panel">
                    <h3>Scaled relative to target loading</h3>
                    @foreach( $reportType->baseRecordType()->records as $record)
                        @include( 'reportTypeRecordRow', [
                            "showFree"=>true,
                            "showTarget"=>true,
                            "record"=>$record,
                            "recordReport"=>$report->recordReports()[ $record->sid ],
                            "scale" => 1/$report->recordReports()[ $record->sid ]->getLoadingTarget( $loadingType )/$report->maxLoadingRatio($loadingType),
                            "target" => $report->recordReports()[ $record->sid ]->getLoadingTarget( $loadingType ),
                            "total" => $report->recordReports()[ $record->sid ]->getLoadingTotal( $loadingType )
                        ])
                    @endforeach
                </div>
                <div id="p3" class="panel">
                    <h3>Scaled relative to allocated loading</h3>
                    @foreach( $reportType->baseRecordType()->records as $record)
                        @include( 'reportTypeRecordRow', [
                            "showFree"=>false,
                            "showTarget"=>false,
                            "record"=>$record,
                            "recordReport"=>$report->recordReports()[ $record->sid ],
                            "scale" => 1/$report->recordReports()[ $record->sid ]->getLoadingTotal( $loadingType ),
                            "target" => $report->recordReports()[ $record->sid ]->getLoadingTarget( $loadingType ),
                            "total" => $report->recordReports()[ $record->sid ]->getLoadingTotal( $loadingType )
                        ])
                    @endforeach
                </div>
            </div>
        </div>
        <script>
            $(document).ready( function() {
               $("#b1").click(function(){
                   $("#p .panel").hide();
                   $("#b button").removeClass( "btn-primary").addClass("btn-default");
                   $("#p1").show();
                   $("#b1").removeClass( "btn-default" ).addClass( "btn-primary");
               }).click();
                $("#b2").click(function(){
                    $("#p .panel").hide();
                    $("#b button").removeClass( "btn-primary").addClass("btn-default");
                    $("#p2").show();
                    $("#b2").removeClass( "btn-default" ).addClass( "btn-primary");
                });
                $("#b3").click(function(){
                    $("#p .panel").hide();
                    $("#b button").removeClass( "btn-primary").addClass("btn-default");
                    $("#p3").show();
                    $("#b3").removeClass( "btn-default" ).addClass( "btn-primary");
                });
            });
        </script>
    @endforeach

    <div class="panel panel-primary">
        <div class="panel-heading"><b>Rules</b></b></div>

        <div class="panel-body">
            @foreach( $reportType->rules() as $rule )
                <h3>Rule #{{ $rule->rank+1 }}</h3>
                @include( 'dataTable', ['data'=>$rule->data() ])
            @endforeach
        </div>
    </div>


    <div class="panel panel-primary">
        <div class="panel-heading"><b>Records</b></b></div>

        <div class="panel-body">
            @foreach( $reportType->baseRecordType()->records as $record)
                @include( 'inspectRecord', ['record'=>$record ])
            @endforeach
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading"><b>Log</b></b></div>

        <div class="panel-body">
            <p>This is a list of every action triggered on every record.</p>
            @foreach( $reportType->baseRecordType()->records as $record)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        @include( 'dataTable', ['data'=>$record->data() ])
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

@endsection
