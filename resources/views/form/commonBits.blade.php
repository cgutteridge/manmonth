<input name="_token" type="hidden" value="{!! csrf_token() !!}"/>
@if( !empty($returnTo) )
    <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>
@endif