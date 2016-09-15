
<form class="">
    @foreach($fields as $field)
        <div class="form-group">
            @include( "editField.".$field->data["type"], [
                "field"=>$field,
                "value"=>@$values[$field->data["name"]],
                "idPrefix"=>$idPrefix."_".$field->data["name"],
            ])
            @if( $field->description() != null ) {
            <small id="{{$idPrefix}}_{{$field->data["name"]}}_help" class="form-text text-muted">
                {{$field->description()}}
            </small>
            @endif

        </div>
    @endforeach
</form>