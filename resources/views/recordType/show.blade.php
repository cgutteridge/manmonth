@extends('page')

@section('title','View Record Type #'.$recordType->sid." - ".$recordType->name )

@section( 'content' )
    <div class="panel panel-info mm-record-block">
        <div class="panel-heading">
            <a href="/record-types/{{ $recordType->id }}/edit" class="pull-right" title="edit"><span
                        class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
            &nbsp;
        </div>
        <table class="table">
            @include('dataTable',['data'=>$recordType->data])
        </table>
    </div>

    @if( count($recordType->reportTypes ))
        <h3>Report Types</h3>
        <ul>
            @foreach( $recordType->reportTypes as $reportType )
                <li>
                    <a href="/report-types/{{$reportType->id}}">#{{$reportType->sid}} (runs
                        on {{$reportType->baseRecordType()->name}}, {{$reportType->rules()->count()}} rule(s))</a>
                </li>
            @endforeach
            <li>TODO: Create new record type</li>
        </ul>
    @endif

    <h3>Records</h3>
    <p>TODO: Create new record</p>
    @foreach( $recordType->records as $record )
        @include("record.block",["record"=>$record, "followLink"=>"none"])
    @endforeach
@endsection


