@extends('page')

@section('title')
    Report Type @title($reportType)
@endsection
@section( 'content')

    @foreach( $reportData as $loadingType=>$loadingData )
        <div class="panel panel-primary">
            <div class="panel-heading">
                <b>Report: {{$loadingData["title"]}}</b>
            </div>

            <div class="panel-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    @foreach( $loadingData["views"] as $viewId=>$view)
                        <li role="presentation"
                            class="{{ (isset($view['first'])&&$view['first'])?"active":""}}"
                        ><a href="#{{$loadingType}}_{{$viewId}}"
                            aria-controls="{{$loadingType}}_{{$viewId}}"
                            role="tab" data-toggle="tab">{{ $view["tabTitle"] }}</a>
                    @endforeach
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    @foreach( $loadingData["views"] as $viewId=>$view)
                        <div role="tabpanel"
                             class="tab-pane{{ (isset($view['first'])&&$view['first'])?"
                             active":"" }}" id="{{$loadingType}}_{{$viewId}}">
                            <h3>{{$view["title"]}}</h3>
                            @foreach( $view['rows'] as $row )
                                @include( 'reportType.recordRow', $row )
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="panel panel-primary">
        <div class="panel-heading"><b>Rules</b></div>
        <div class="panel-body">
            @foreach( $reportType->rules() as $rule )
                <div class="panel panel-info mm-record-block">
                    <div class="panel-heading ">
                        <a href="@url($rule,'edit')" class="pull-right" title="edit"><span
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

@endsection
