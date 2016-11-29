<table class="mm-record mm-record-entity mm-record-{{$record->sid}}">
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
        @if( $swimLanes )
            @foreach( $links as $link )
                <th>{{$link["title"]}}</th>
            @endforeach
        @endif
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <table class="mm-record-data">
                @foreach( $data as $field )
                    <tr style='cursor:pointer;'
                        data-toggle="tooltip"
                        title="Click to edit"
                        onclick="document.location.href = '@url($record, 'edit', ["_mmreturn"=>$returnURL])';">
                        <th>{{$field["title"]}}:</th>
                        <td>@if(!empty($field['value']))
                                {{$field["value"]}}
                            @elseif(isset($field['default']))
                                {{$field["default"]}} <span class="mm-default">Default</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @foreach( $links as $link )
                    <tr>
                        <th>{{$link["title"]}}:</th>
                        <td>
                            @foreach( $link["records"] as $subrecord)
                                <a
                                        href="@url($subrecord["record"])"
                                        data-rid="{{$subrecord["record"]->sid}}"
                                        class="mm-record-stub mm-record-entity mm-record-{{$subrecord["record"]->sid}}"
                                >
                                    @title( $subrecord["record"])
                                </a>
                            @endforeach

                            <a class="mm-button" href="{{$link["createLink"]}}">
                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
        @if( $swimLanes )
            @foreach( $links as $link )
                <td>
                    @foreach( $link["records"] as $record)
                        @include( "record.block", $record )
                    @endforeach
                </td>
            @endforeach
        @endif
    </tr>
    </tbody>
</table>
