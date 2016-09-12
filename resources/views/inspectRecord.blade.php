<table class="mm_inspect_record">
<tr>
    <td class="mm_inspect_record_record">

        @include( 'dataTable', ['data'=>$record->data() ])
    </td>
    <td>
        <table class="mm_inspect_links">
            @foreach( $record->forwardLinks as $link )
            <tr>
                <td>&rarr; {{ $link->linkType->name }} &rarr;</td>
                <td>
                    @include( 'inspectRecord', ['record'=>$link->objectRecord ])
                </td>
            </tr>
            @endforeach
        </table>
    </td>
</tr>
</table>
