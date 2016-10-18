@extends('page')

@section('title')
    View @title($record->recordType): @title($record)
@endsection

@section( 'content')
    @foreach( $reports as $report )
        @foreach( $report->getLoadingTypes() as $loadingType )
            @include( 'reportType.recordRow', [
            "showFree"=>true,
            "showTarget"=>true,
            "record"=>$record,
            "recordReport"=>$report,
            "scale" =>
                0 == 1/max( $report->getLoadingTotal($loadingType), $report->getLoadingTarget($loadingType) ) ?
                1 :
                1/max( $report->getLoadingTotal($loadingType), $report->getLoadingTarget($loadingType) )
            ,
            "target" => $report->getLoadingTarget( $loadingType ),
            "total" => $report->getLoadingTotal( $loadingType )
            ])
        @endforeach
    @endforeach
    @include("record.block",$recordBlock)
@endsection
