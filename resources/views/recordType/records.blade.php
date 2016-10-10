@extends('page')

@section('title')
    View Records of Type @title($recordType)
@endsection

@section( 'content' )
    <p>TODO: Create new record</p>
    @foreach( $recordType->records as $record )
        @include("record.block",["record"=>$record, "followLink"=>"none","editParams"=>["_mmreturn"=>(new \App\Http\LinkMaker())->url($recordType,"records")]])
    @endforeach
@endsection


