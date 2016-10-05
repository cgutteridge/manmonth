@inject('linkMaker','App\Http\LinkMaker' )
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
                <a href="{{ $linkMaker->link( $reportType ) }}">{{$reportType->name}} #{{$reportType->sid}} (runs
                    on {{$reportType->baseRecordType()->name}}, {{$reportType->rules()->count()}} rule(s))</a>
            </li>
        @endforeach
        <li>TODO: Create new record type</li>
    </ul>

    <h3>Record Types</h3>
    <ul>
        @foreach( $documentRevision->recordTypes as $recordType )
            <li>
                <a href="{{ $linkMaker->link( $recordType ) }}">{{$recordType->title()}}</a>
            </li>
        @endforeach
        <li>TODO: Create new record type</li>
    </ul>

    <h3>Link Types</h3>
    <ul>
        @foreach( $documentRevision->linkTypes as $linkType )
            <li>
                <a href="{{ $linkMaker->link( $linkType ) }}">{{$linkType->title()}}</a>
            </li>
        @endforeach
        <li>TODO: Create new record type</li>
    </ul>

@endsection
