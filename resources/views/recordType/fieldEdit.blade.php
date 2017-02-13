@extends('page')

@section('title')
    Edit Field: @title($recordType)/@title($field)
@endsection

@section( 'content')
    <form method="post" action="@url($field)">
        <table class='mm-form-fields'>
            @include( 'editField.index', $meta )
            <tr>
                <th>
                </th>
                <td>
                    <div class='mm-form-buttons'>
                        <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
                        <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
                    </div>
                </td>
            </tr>
        </table>
        <input name="_method" type="hidden" value="PUT"/>
        @include('form.commonBits', ["returnTo"=>$returnTo])
    </form>
@endsection
