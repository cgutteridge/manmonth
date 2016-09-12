@extends('page')

@section('title','Report Type')

@section( 'content')
    <h2>Target Record Type</h2>
    {{ $reportType->baseRecordType()->name }}
    <h2>Rules</h2>
    @foreach( $reportType->rules() as $rule )
        <h3>Rule #{{ $rule->rank+1 }}</h3>
        @include( 'dataTable', ['data'=>$rule->data() ])
    @endforeach
    <h2>Records</h2>
    @foreach( $reportType->baseRecordType()->records as $record)
        @include( 'inspectRecord', ['record'=>$record ])
    @endforeach

    @foreach( $report->loadingTypes() as $loadingType )
        <h2>Report: {{$loadingType}}</h2>
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
    @endforeach

@endsection
