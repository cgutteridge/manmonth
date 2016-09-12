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

        @foreach( $reportType->baseRecordType()->records as $record)
            @include( 'reportTypeRecordRow', [
                "record"=>$record,
                "recordReport"=>$report->recordReports()[ $record->sid ],
                "scaleSize" => max( $report->maxLoading($loadingType), $report->maxTarget($loadingType) ),
                "target" => $report->recordReports()[ $record->sid ]->getLoadingTarget( $loadingType ),
                "total" => $report->recordReports()[ $record->sid ]->getLoadingTotal( $loadingType )
            ])
        @endforeach
    @endforeach

@endsection
