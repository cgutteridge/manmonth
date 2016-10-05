@inject('linkMaker','App\Http\LinkMaker' )
@extends('page')

@section('title','Report 23')

@section( 'content')
    {{ print_r( $report->recordReports(),1) }}
@endsection
