@include( 'dataTable', ['data'=>$record->data() ])
@foreach( $record->forwardLinks as $link )
    <div style="border:1px solid orange; padding:0.5em; margin:0.5em">
        <div>LINK: {{ $link->linkType->name }}</div>
        @include( 'inspectRecord', ['record'=>$link->objectRecord ])
    </div>
@endforeach