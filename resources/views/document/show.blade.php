@extends('page')

@section('title')
    @title($document)
@endsection
@section( 'content')
    @include( 'dataTable', [ "data"=>[
        "name"=>$document->name,
        "created_at"=>$document->created_at,
        "updated_at"=>$document->updated_at,
    ]])
    <div class="row">
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
        <div class="clearfix"></div>
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
    </div>

@endsection
