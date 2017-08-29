@extends('page')

@section('title')
    {{$actionLabel}} {{$subjectLabel}}?
@endsection
@section( 'content')

    <form method="post" action="{{$action}}">
        <div class='mm-form-buttons'>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="confirm">{{$actionLabel}}</button>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
        </div>
        @include('form.commonBits')
    </form>

@endsection

