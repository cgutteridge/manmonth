@extends('page')

@section('title','Edit Record #'.$record->sid)

@section( 'content')
    <form method="post" action="/records/{{$record->id}}">
        @include( 'editFields', [
            "fields"=>$record->recordType->fields(),
            "values"=>$record->data,
            "idPrefix"=>$idPrefix,
    ])
        <input name="_token" type="hidden" value="{!! csrf_token() !!}" />
        <input name="_method" type="hidden" value="PUT" />
        <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>
        <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
        <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
    </form>
@endsection