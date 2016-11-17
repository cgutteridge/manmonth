@extends('page')

@section('title')
    Create Record of type @title($record->recordType)
@endsection
@section( 'content')
    <form method="post" action="@url($record->recordType,'create-record')">
        <table class='mm-form-fields'>
            @include( 'editFields', [
                "fields"=>$record->recordType->fields(),
                "values"=>$record->data,
                "idPrefix"=>$idPrefix."field_",
            ])
            @include( 'record.editLinks' ,[
                "values"=>$record->data,
                "idPrefix"=>$idPrefix."link_",
                "linkChanges"=>$linkChanges
            ])
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
        @include('form.commonBits', ["returnTo"=>$returnTo])
    </form>
@endsection
