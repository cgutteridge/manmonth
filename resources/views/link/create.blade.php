@extends('page')

@section('title')
    Create "@title($link->linkType->domain) @title($link->linkType) @title($link->linkType->range)" link
@endsection
@section( 'content')
    <form method="post" action="@url($link->linkType,'create-link')">
        @include("link.form",[
                    "idPrefix"=>$idPrefix,
                    "link"=>$link
                    ])
        <div class='mm-form-buttons'>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
        </div>
        @include('form.commonBits', ["returnTo"=>$returnTo])
    </form>
@endsection
