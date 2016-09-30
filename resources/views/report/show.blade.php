@extends('page')

@section('title','Report 23')

@section('context')
    @include('documentRevision.contextBar',['documentRevision'=>$report->documentRevision])
@endsection

@section( 'content')
    {{ print_r( $report->recordReports(),1) }}
@endsection
