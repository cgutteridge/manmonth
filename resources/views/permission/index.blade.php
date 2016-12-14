@extends('page')

@section('title','List of permissions')

@section( 'content')
    <p>This page is a reference of all the possible permissions that can be assigned to a role.</p>
    <h2>Global permissions</h2>
    @include('permission.list',['list'=>$globalPermissions])
    <h2>Document permissions</h2>
    @include('permission.list',['list'=>$documentPermissions])

@endsection
