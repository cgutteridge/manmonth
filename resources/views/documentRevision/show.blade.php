@extends('page')

@section('title', $documentRevision->document->name.' rev #'.$documentRevision->id." (".$documentRevision->status.")");

@section('content')
    @include( 'dataTable', [ "data"=>[
        "status"=>$documentRevision->status,
        "created_at"=>$documentRevision->created_at,
        "updated_at"=>$documentRevision->updated_at,
    ]])

    <h3>Report Types</h3>
    <ul>
        @foreach( $documentRevision->reportTypes as $reportType )
            <li>
                <a href="/report-types/{{$reportType->id}}">#{{$reportType->sid}} (runs on {{$reportType->baseRecordType()->name}}, {{$reportType->rules()->count()}} rule(s))</a>
            </li>
        @endforeach
    </ul>
@endsection