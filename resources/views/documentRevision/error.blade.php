@inject("dateMaker","App\Http\DateMaker")
@extends('page')

@section('title')
    @title($documentRevision->document) rev #{{$documentRevision->id}} ({{$documentRevision->status}}) ERROR
@endsection

@section('content')
<p>Request could not be performed.</p>
@endsection
