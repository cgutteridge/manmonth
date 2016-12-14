@extends('page')

@section('title')
    @title($document)
@endsection
@section( 'content')
    <table class="mm-datatable">

        @include( 'dataTable', [ "data"=>[
            "name"=>$document->name,
            "created_at"=>$document->created_at,
            "updated_at"=>$document->updated_at,
        ]])
    </table>

    <div class="row" style="margin-top:1em">
        @can("view-current",$document)
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        Current Revision
                    </div>
                    <div class="row panel-body">
                        @include( 'document.revisionList',[
                        "document"=>$document,
                        "showStatus"=>"current"])
                    </div>
                </div>
            </div>
        @endcan
        @can("view-draft",$document)
            <div class="col-md-6">
                @if($document->draftRevision())
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            Active Draft Revision
                        </div>
                        <div class="row panel-body">
                            @include( 'document.revisionList',[
                            "document"=>$document,
                            "showStatus"=>"draft"])
                        </div>
                    </div>
                @endif
            </div>
        @endcan
        @can("view-archive",$document)
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Archived Revisions
                    </div>
                    <div class="row panel-body">
                        @include( 'document.revisionList',[
                        "document"=>$document,
                        "showStatus"=>"archive"])
                    </div>
                </div>
            </div>
        @endcan
        @can("view-scrap",$document)
            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        Scrapped Revisions
                    </div>
                    <div class="row panel-body">
                        @include( 'document.revisionList',[
                        "document"=>$document,
                        "showStatus"=>"scrap"])
                    </div>
                </div>
            </div>
        @endcan
    </div>

@endsection
