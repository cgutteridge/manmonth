@inject('linkMaker','App\Http\LinkMaker' )
@extends('page')

@section('title','Create Record of type '.$record->recordType->name)

@section( 'content')
    <form method="post" action="{{$linkMaker->link($record->recordType)}}/create-record">
        @include( 'editFields', [
            "fields"=>$record->recordType->fields(),
            "values"=>$record->data,
            "idPrefix"=>$idPrefix,
        ])
        <input name="_token" type="hidden" value="{!! csrf_token() !!}"/>
        <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>
        <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
        <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
    </form>
@endsection