@extends('page')

@section('title','View Record Type #'.$record->sid)

@section( 'content')
    {{ dump( $recordtype ) }} ) }}
    <div><a href="/recordtypes/{{ $recordtype->id }}/edit">Edit</a></div>
@endsection