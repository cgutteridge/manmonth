<table class="mm-record">
    <thead>
    <tr>
        <th>
            <a href="@url($record)" data-toggle="tooltip" title="Focus on this @title($record->recordType)">
                @title($record->recordType)
            </a>
            <a href="@url($record, 'edit', ["_mmreturn"=>$returnURL])" class="pull-right" data-toggle="tooltip"
               title="Edit this @title($record->recordType)">
                <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
            </a>
        </th>
        @foreach( $links as $link )
            <th>{{$link["title"]}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style='cursor:pointer;'
            onclick="document.location.href = '@url($record, 'edit', ["_mmreturn"=>$returnURL])';">
            <table class="mm-record-data">
                @foreach( $data as $field )
                    <tr>
                        <th>{{$field["title"]}}:</th>
                        <td>{{$field["value"]}}</td>
                    </tr>
                @endforeach
            </table>
        </td>
        @foreach( $links as $link )
            <td>
                @foreach( $link["records"] as $record)
                    @include( "record.block", $record )
                @endforeach
            </td>
        @endforeach
    </tr>
    </tbody>
</table>