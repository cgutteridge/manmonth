@extends('page')

@section('title')
    View @title($record->recordType): @title($record)
@endsection

@section( 'content')
    @foreach( $reports as $report )
        @foreach( $report->getLoadingTypes() as $loadingType )
            <table style="width:100%">
                @include( 'reportType.recordRow', [
                "showFree"=>true,
                "showTarget"=>true,
                "record"=>$record,
                "recordReport"=>$report,
                "loadingType"=>$loadingType,
                "loadings"=>$report->getLoading($loadingType),
                "units" => $report->getLoadingOption($loadingType, "units"),
                "scale" =>
                    0 == 1/max( $report->getLoadingTotal($loadingType), $report->getLoadingTarget($loadingType) ) ?
                    1 :
                    1/max( $report->getLoadingTotal($loadingType), $report->getLoadingTarget($loadingType) )
                ,
                "target" => $report->getLoadingTarget( $loadingType ),
                "total" => $report->getLoadingTotal( $loadingType )
                ])
            </table>
        @endforeach
    @endforeach
    @include("record.block",$recordBlock)
    <p>
        @can( 'edit', $record )
            <a type="button" class="btn btn-primary" href="@url($record,'edit')">
                Edit this @title($record->recordType)
            </a>
        @endcan
        @can( 'edit', $record )
            <a type="button" class="btn btn-primary" href="@url($record,'delete')">
                Delete this @title($record->recordType)
            </a>
        @endcan
    </p>
@endsection
