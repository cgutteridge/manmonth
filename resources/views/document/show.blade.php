@inject("dateMaker","App\Http\DateMaker")
@extends('page')

@section('title')
    @title($document)
@endsection
@section( 'content')

    {{--<table class="mm-datatable">--}}
    {{--@include( 'dataTable', [ "data"=>[--}}
    {{--"name"=>$document->name,--}}
    {{--"created_at"=>$dateMaker->dateTime($document->created_at),--}}
    {{--"updated_at"=>$dateMaker->dateTime($document->updated_at),--}}
    {{--]])--}}
    {{--</table>--}}


    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                Revisions
            </div>
            <div class="panel-body">
                @can('commit', $document)
                    @if($draftStatus=="none")
                        <p>
                            <a type="button" class="btn btn-primary"
                               href="@url($document,'create-draft')">
                                Start new revision
                            </a>
                        </p>
                    @elseif( $draftStatus=="mine" )
                        <p>You have a revision of this document checked-out to edit. Nobody else will be able to edit
                            until you commit or scrap your revision.</p>
                    @else
                        <p>This document is being edited by {{$draftOwner}}. You won't be able to modify it until they commit or scrap their revision.</p>
                    @endif
                @endcan

                @if(0==count($revisions['archive'])+count($revisions['draft']))
                    <p>You do not have permissions suitable to see any revisions.</p>
                @else
                    <table class="mm-datatable">
                        <tr class="mm-datatable-headingrow">
                            <th>Timestamp</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Comment</th>
                        </tr>
                        @include( 'document.revisionList',["revisions"=>$revisions['draft']])
                        @include( 'document.revisionList',["revisions"=>$revisions['archive']])
                    </table>
                @endif
            </div>
        </div>
    </div>

    @if(count($revisions['scrap'])>0)
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    Scrapped revisions
                </div>
                <div class="panel-body">
                    <table class="mm-datatable">
                        <tr class="mm-datatable-headingrow">
                            <th>Timestamp</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Comment</th>
                        </tr>
                        @include( 'document.revisionList',["revisions"=>$revisions['scrap']])
                    </table>
                </div>
            </div>
        </div>
        @endif
        </div>

@endsection
