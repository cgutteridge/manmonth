@inject('linkMaker','App\Http\LinkMaker' )
@extends('page')

@section('title','View '.$record->recordType->bestTitle().": ".$record->title() )

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
    @include("record.block",[
        'record'=>$record,
        'followLink'=>'all',
        'seen'=>[],
        'editParams'=>['_mmreturn'=>$linkMaker->link($record)]
    ])
@endsection
