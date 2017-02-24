@inject("titleMaker","App\Http\TitleMaker")
@foreach($fields as $field)
    @if( $field->editable() )
        @include( "editField.".$field->data["type"], [
            "field"=>$field,
            "value"=>@$values[$field->data["name"]],
            "placeholder"=>(array_key_exists("default",$field->data)
            ?"Default '".$field->data["default"]."'"
            :"Enter ".$titleMaker->title($field)),
            "idPrefix"=>$idPrefix.$field->data["name"],
        ])
        @if( $field->getMode() != "only_local")
            <tr>
                <th></th>
                <td>External value:
                    @include('showField.field',["field"=>$field,"value"=>@$externalValues[$field->data["name"]])
                </td>
            </tr>
        @endif
    @endif
@endforeach
