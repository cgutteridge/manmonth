@extends('page')

@section('title')
    View Link Type @title($linkType)
@endsection
@section( 'content' )

    <table class="mm-datatable">
            @include('dataTable',[ "data"=>$data] )
        </table>

@endsection


