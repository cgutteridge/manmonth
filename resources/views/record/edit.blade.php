@extends('page')

@section('title')
    Edit Record: @title($record)
@endsection

@section( 'content')
    <form method="post" action="@url($record)">
        @include( 'editFields', [
            "fields"=>$record->recordType->fields(),
            "values"=>$record->data,
            "idPrefix"=>$idPrefix,
    ])
        <input name="_token" type="hidden" value="{!! csrf_token() !!}"/>
        <input name="_method" type="hidden" value="PUT"/>
        <input name="_mmreturn" type="hidden" value="{{ $returnTo }}"/>

        @foreach( $record->recordType->forwardLinkTypes as $linkType )
            @if( isset($linkType->range_min) && $linkType->range_min==1 )
                <div>
                    {{$linkType}}
                    SHOW EXISTING LINKS.
                    ADD NEW
                    <LINKS class=""></LINKS>
                </div>
            @endif
        @endforeach
        @foreach( $record->recordType->backLinkTypes as $linkType )
            @if( isset($linkType->domain_min) && $linkType->domain_min==1 )
                <div>
                    {{$linkType}}
                </div>
            @endif
        @endforeach

        <button type="submit" class="btn btn-primary" name="_mmaction" value="save">Save</button>
        <button type="submit" class="btn btn-primary" name="_mmaction" value="cancel">Cancel</button>
    </form>
@endsection