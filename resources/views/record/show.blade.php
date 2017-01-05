@extends('page')

@section('title')
    View @title($record->recordType): @title($record)
@endsection

@section( 'content')
    @foreach( $reports as $report )
        <div>
            @include( 'reportType.recordGraph', [
            "showFree"=>true,
            "showTarget"=>true,
            "record"=>$record,
            "recordReport"=>$report,
            "loadings"=>$report->getLoadings(),
            "units" => $report->getOption( "units"),
            "scale" =>
                0 == 1/max( $report->getLoadingTotal(), $report->getLoadingTarget() ) ?
                1 :
                1/max( $report->getLoadingTotal(), $report->getLoadingTarget() )
            ,
            "target" => $report->getLoadingTarget(),
            "total" => $report->getLoadingTotal()
            ])
        </div>
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
