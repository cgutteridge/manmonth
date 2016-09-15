@extends('page')

@section('title','Edit Record #'.$record->sid)

@section( 'content')
@include( 'form', [
  "fields"=>$record->recordType->fields(),
  "values"=>$record->data,
  "idPrefix"=>$idPrefix,
])
@endsection