@extends('page')

@section('title')
    Create "@title($link->linkType->domain) @title($link->linkType) @title($link->linkType->range)" link
@endsection
@section( 'content')
    <form method="post" action="@url($link->linkType,'create-link')">
        <input name="_token" type="hidden" value="{!! csrf_token() !!}"/>
        <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>
        @include("link.form",[
                    "idPrefix"=>$idPrefix,
                    "link"=>$link
                    ])
        <div class='mm-form-buttons'>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
        </div>
    </form>
@endsection
