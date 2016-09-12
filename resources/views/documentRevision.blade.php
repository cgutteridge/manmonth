@extends('page')

@section('title','Document Revision')

@section( 'content')
    {{ print_r( $documentRevision,1) }}

@endsection
