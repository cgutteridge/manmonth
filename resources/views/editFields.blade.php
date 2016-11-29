@inject("titleMaker","App\Http\TitleMaker")
@foreach($fields as $field)
    @include( "editField.".$field->data["type"], [
        "field"=>$field,
        "value"=>@$values[$field->data["name"]],
        "placeholder"=>(array_key_exists("default",$field->data)
        ?"Default '".$field->data["default"]."'"
        :"Enter ".$titleMaker->title($field)),
        "idPrefix"=>$idPrefix.$field->data["name"],
    ])
@endforeach
