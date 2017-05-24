@if(!empty($field->data["prefix"])){{$field->data["prefix"]}}@endif
@if(!isset($value))<span class="mm-default">NULL</span>@elseif(View::exists('showField.'.$field->data["type"]))
    @include( "showField.".$field->data["type"], [ "field"=>$field,"value"=>$value])
@else{!! preg_replace( '/\n/', "<br>\n", htmlspecialchars($value)) !!}@endif
@if(!empty($field->data["suffix"])){{$field->data["suffix"]}}@endif