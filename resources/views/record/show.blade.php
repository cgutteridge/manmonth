@extends('page')

@section('title')
    View @title($record->recordType): @title($record)
@endsection

@section( 'content')
    @foreach( $reports as $rinfo )
        <div>
            @include( 'reportType.recordGraph', [
            "showFree"=>true,
            "showTarget"=>true,
            "record"=>$record,
            "recordReport"=>$rinfo["report"],
            "loadings"=>$rinfo["report"]->getLoadings(),
            "units" => $rinfo["report"]->getOption( "units"),
            "scale" =>
                0 == 1/max( $rinfo["report"]->getLoadingTotal(), $rinfo["report"]->getLoadingTarget() ) ?
                1 :
                1/max( $rinfo["report"]->getLoadingTotal(), $rinfo["report"]->getLoadingTarget() )
            ,
            "target" => $rinfo["report"]->getLoadingTarget(),
            "total" => $rinfo["report"]->getLoadingTotal(),
            "categories" => $rinfo["categories"]
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
