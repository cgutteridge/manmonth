@if(!isset($value))
    <span class="mm-default">NULL</span>
@elseif(View::exists('showField.'.$field->data["type"]))
    @include( "showField.".$field->data["type"], [ "field"=>$field,"value"=>$value])
@else
    {{$value}}
@endif