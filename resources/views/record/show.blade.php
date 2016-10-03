@inject('linkMaker','App\Http\Controllers\LinkMaker')
@extends('page')

@section('title','View Record #'.$record->sid)

@section( 'content')
    @foreach( $reports as $report )
        @foreach( $report->getLoadingTypes() as $loadingType )
            @include( 'reportType.recordRow', [
            "showFree"=>true,
            "showTarget"=>true,
            "record"=>$record,
            "recordReport"=>$report,
            "scale" => 1/max( $report->getLoadingTotal($loadingType), $report->getLoadingTarget($loadingType) ),
            "target" => $report->getLoadingTarget( $loadingType ),
            "total" => $report->getLoadingTotal( $loadingType )
            ])
        @endforeach
    @endforeach
    @include("record.block",[
        'record'=>$record,
        'followLink'=>'all',
        'seen'=>[],
        'editParams'=>['_mmreturn'=>$linkMaker->link($record)]
    ])
@endsection