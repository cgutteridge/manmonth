@inject('linkMaker','App\Http\LinkMaker' )
@extends('page')

@section('title','View Records of Type #'.$recordType->sid." - ".$recordType->name )

@section( 'content' )
    <p>TODO: Create new record</p>
    @foreach( $recordType->records as $record )
        @include("record.block",["record"=>$record, "followLink"=>"none","editParams"=>["_mmreturn"=>$linkMaker->link($recordType)]])
    @endforeach
@endsection


