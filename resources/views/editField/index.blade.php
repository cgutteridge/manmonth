@inject("titleMaker","App\Http\TitleMaker")
@foreach($fields as $field)
    @if($field->getMode() == 'only_external')
        <tr>
            <th>
                <label>@title($field):</label>
            </th>
            <td>
                This field comes from an external source.
            </td>
        </tr>
    @elseif( $field->editable() )
        @include( "editField.".$field->data["type"], [
            "field"=>$field,
            "value"=>@$values[$field->data["name"]],
            "placeholder"=>(array_key_exists("default",$field->data)
            ?"Default '".$field->data["default"]."'"
            :"Enter ".$titleMaker->title($field)),
            "idPrefix"=>$idPrefix.$field->data["name"],
        ])
    @else
        @include( "editField.uneditable", [
            "field"=>$field,
            "value"=>@$values[$field->data["name"]]
            ])
    @endif
@endforeach
