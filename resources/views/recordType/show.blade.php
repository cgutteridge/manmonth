@inject('linkMaker','App\Http\LinkMaker' )
@extends('page')

@section('title','View Record Type #'.$recordType->sid." - ".$recordType->name )

@section( 'content' )
    <div class="panel panel-info mm-record-block">
        <div class="panel-heading">
            <a href="{{ $linkMaker->edit($recordType) }}" class="pull-right" title="edit"><span
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
                    <a href="{{ $linkMaker->link($reportType) }}">#{{$reportType->sid}} (runs
                        on {{$reportType->baseRecordType()->name}}, {{$reportType->rules()->count()}} rule(s))</a>
                </li>
            @endforeach
            <li>TODO: Create new record type</li>
        </ul>
    @endif

@endsection


