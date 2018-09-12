@extends('page')

@section('title')
     {{$actionLabel}}?
@endsection
@section( 'content')
    <p>{{$subjectLabel}}</p>
    <form method="post" action="{{$action}}">
        @if( isset($formFields))
            <table class='mm-form-fields'>
                @include( 'editField.index',$formFields )
            </table>
        @endif
        <div class='mm-form-buttons'>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="confirm">{{$actionLabel}}</button>
            <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
        </div>
        @include('form.commonBits')
    </form>

@endsection

