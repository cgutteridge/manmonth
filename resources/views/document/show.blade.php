@inject("dateMaker","App\Http\DateMaker")
@extends('page')

@section('title')
    @title($document)
@endsection
@section( 'content')

    <table class="mm-datatable">
        @include( 'dataTable', [ "data"=>[
            "name"=>$document->name,
            "created_at"=>$dateMaker->dateTime($document->created_at),
            "updated_at"=>$dateMaker->dateTime($document->updated_at),
        ]])
    </table>

    <div class="row" style="margin-top:1em">
        @can("view-draft",$document)
            <div class="col-md-12">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        Draft revision
                    </div>
                    <div class="panel-body">
                        @if(count($revisions['draft']))

                            @include( 'document.revisionList',["revisions"=>$revisions['draft']])

                        @else
                            @can('commit', $document)
                                <p>
                                    <a type="button" class="btn btn-primary"
                                       href="@url($document,'create-draft')">
                                        Create new draft
                                    </a>
                                </p>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        @endcan
        @can("view-archive",$document)
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Committed revisions
                    </div>
                    <div class="panel-body">
                        @include( 'document.revisionList',["revisions"=>$revisions['archive']])
                    </div>
                </div>
            </div>
        @endcan
        @can("view-scrap",$document)
            <div class="col-md-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        Scrapped revisions
                    </div>
                    <div class="panel-body">
                        @include( 'document.revisionList',["revisions"=>$revisions['scrap']])
                    </div>
                </div>
            </div>
        @endcan
    </div>

@endsection
