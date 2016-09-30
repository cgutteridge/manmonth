@inject('linkMaker','App\Http\Controllers\LinkMaker')

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
                        @include( 'reportType.recordRow', [
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
                        @include( 'reportType.recordRow', [
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
                        @include( 'reportType.recordRow', [
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
            $(document).ready(function () {
                $("#b1").click(function () {
                    $("#p .panel").hide();
                    $("#b button").removeClass("btn-primary").addClass("btn-default");
                    $("#p1").show();
                    $("#b1").removeClass("btn-default").addClass("btn-primary");
                }).click();
                $("#b2").click(function () {
                    $("#p .panel").hide();
                    $("#b button").removeClass("btn-primary").addClass("btn-default");
                    $("#p2").show();
                    $("#b2").removeClass("btn-default").addClass("btn-primary");
                });
                $("#b3").click(function () {
                    $("#p .panel").hide();
                    $("#b button").removeClass("btn-primary").addClass("btn-default");
                    $("#p3").show();
                    $("#b3").removeClass("btn-default").addClass("btn-primary");
                });
            });
        </script>
    @endforeach

    <div class="panel panel-primary">
        <div class="panel-heading"><b>Rules</b></div>
        <div class="panel-body">
            @foreach( $reportType->rules() as $rule )
                <div class="panel panel-info mm-record-block">
                    <div class="panel-heading ">
                        <a href="{{ $linkMaker->edit($rule) }}" class="pull-right" title="edit"><span
                                    class="glyphicon glyphicon-edit"></span></a>
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
    <script type='text/javascript'>
        var hovernote;
        jQuery(document).ready(function () {
            if (!hovernote) {
                hovernote = jQuery("<div id='hovernote' style='display:none'></div>").appendTo('body');
                hovernote.mouseleave(function () {
                    jQuery('#hovernote').hide();
                });
            }
            var ua = navigator.userAgent;
            var mobile = ua.match(/(iPhone|iPod|iPad|BlackBerry|Android)/);
            if (mobile) {
                jQuery('.programme_event').css('border', 'solid 1px green');
            }
            jQuery('.mm_loading').map(function (i, x) {
                var cell = jQuery(x);

                var shownote_fn = function (event) {
                    hovernote.html(cell.find(".hover").html());
                    var tPosX = event.pageX - 190;
                    var tPosY = event.pageY - 10;
                    hovernote.css({
                        'position': 'absolute',
                        'width': '300px',
                        'white-space': 'normal',
                        'min-height': '20px',
                        'top': tPosY + 'px',
                        'left': tPosX + 'px',
                        'display': 'block'
                    });

                    var BOTTOM_MARGIN = 15;
                    var RIGHT_MARGIN = 15;
                    // check to see if box would be off right hand side and if so
                    // shunt it back a bit
                    if (tPosX + hovernote.width() > jQuery(window).innerWidth() + jQuery(window).scrollLeft() - RIGHT_MARGIN) {
                        tPosX = jQuery(window).innerWidth() + jQuery(window).scrollLeft() - hovernote.width() - RIGHT_MARGIN;
                        hovernote.css('left', tPosX + 'px');
                    }
                    // and the left
                    if (tPosX < jQuery(window).scrollLeft() + RIGHT_MARGIN) {
                        tPosX = jQuery(window).scrollLeft() + RIGHT_MARGIN;
                        hovernote.css('left', tPosX + 'px');
                    }
                    // check to see if box would be off the bottom of the window and if so
                    // shunt it up a bit
                    if (tPosY + hovernote.height() > jQuery(window).innerHeight() + jQuery(window).scrollTop() - BOTTOM_MARGIN) {
                        tPosY = jQuery(window).innerHeight() + jQuery(window).scrollTop() - hovernote.height() - BOTTOM_MARGIN;

                        hovernote.css('top', tPosY + 'px');
                    }
                };
                if (!mobile) {
                    cell.mouseenter(shownote_fn);
                }

            });
        });
    </script>


@endsection
