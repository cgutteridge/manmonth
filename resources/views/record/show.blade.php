@extends('page')

@section('title','View Record #'.$record->sid)

@section('context')
    @include('documentRevision.contextBar',['documentRevision'=>$record->documentRevision])
@endsection

@section( 'content')
    @include("record.block",['record'=>$record, 'followLink'=>'all'])
@endsection