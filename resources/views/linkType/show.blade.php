@extends('page')

@section('title')
    View Link Type @title($linkType)
@endsection
@section( 'content' )
    <div class="panel panel-info mm-record-block">
        <div class="panel-heading">
            <a href="@url($linkType,'edit')" class="pull-right" title="edit"><span
                        class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
            &nbsp;
        </div>
        <table class="table mm-table">
            @include('dataTable',[ "data"=>$data] )
        </table>
    </div>


@endsection


