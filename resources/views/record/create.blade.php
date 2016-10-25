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
                "idPrefix"=>$idPrefix."link_"
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
        <input name="_token" type="hidden" value="{!! csrf_token() !!}"/>
        <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>


    </form>
@endsection
