@extends('page')

@section('title')
    Delete Record: @title($record)
@endsection

@section( 'content')
    <form method="post" action="@url($record)">
        <p>Delete this and any dependent records?</p>
        <table class='mm-form-fields'>
            <td>
                <div class='mm-form-buttons'>
                    <button type="submit" class="btn btn-primary" name="_mmaction" value="delete">Delete</button>
                    <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
                </div>
            </td>
            </tr>
        </table>
        <input name="_method" type="hidden" value="DELETE"/>
        @include('form.commonBits', ["returnTo"=>$returnTo])
    </form>
@endsection
