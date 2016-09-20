@extends('page')

@section('title','View Record #'.$record->sid)

@section( 'content')
    @include("inspectRecord",['record'=>$record])
    <div><a href="/records/{{ $record->id }}/edit">Edit</a></div>
@endsection