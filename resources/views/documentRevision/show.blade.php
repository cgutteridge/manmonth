@extends('page')

@section('title', $documentRevision->document->name.' rev #'.$documentRevision->id." (".$documentRevision->status.")")

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
        <li>TODO: Create new record type</li>
    </ul>

    <h3>Record Types</h3>
    <ul>
        @foreach( $documentRevision->recordTypes as $recordType )
            <li>
                <a href="/record-types/{{$recordType->id}}">#{{$recordType->sid}} {{$recordType->name}}</a>
            </li>
        @endforeach
        <li>TODO: Create new record type</li>
    </ul>

    <h3>Link Types</h3>
    <ul>
        @foreach( $documentRevision->linkTypes as $linkType )
            <li>
                <a href="/link-types/{{$linkType->id}}">#{{$linkType->sid}} {{$linkType->name}}</a>
            </li>
        @endforeach
        <li>TODO: Create new record type</li>
    </ul>

@endsection