<table class="mm-record mm-record-entity mm-record-{{$record->sid}}">
    <thead>
    <tr>
        <th>
            <a href="@url($record)" data-toggle="tooltip" title="Focus on this @title($record->recordType)">
                @title($record->recordType)
            </a>
            @can('edit',$record)
                <a href="@url($record, 'edit', ["_mmreturn"=>$returnURL])" class="pull-right" data-toggle="tooltip"
                   title="Edit this @title($record->recordType)">
                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                </a>
            @endcan
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
            <table class="mm-record-data" style="width:100%">
                @foreach( $data as $fieldValue )
                    <tr style='cursor:pointer;'
                        data-toggle="tooltip"
                        title="Click to view"
                        onclick="document.location.href = '@url($record)';">
                        <th>{{$fieldValue["title"]}}:</th>
                        @if( $fieldValue["source"] == 'default')
                            <td colspan="2">
                                @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["default"]])
                                <span class="mm-default">Default</span>
                            </td>
                        @elseif( $fieldValue["source"] == 'none')
                            <td colspan="2">
                                @include('showField.field',["field"=>$fieldValue['field'],"value"=>null])
                            </td>
                        @elseif($fieldValue["mode"]=="prefer_local")
                            @if( $fieldValue["source"] == 'local')
                                <td style="width:40%">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["local"]])
                                    <span class="mm-default">Local</span>
                                </td>
                                <td style="width:40%">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["external"]])
                                    <span class="mm-default">External</span>
                                </td>
                            @endif
                            @if( $fieldValue["source"] == 'external')
                                <td colspan="2">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["external"]])
                                    <span class="mm-default">External</span>
                                </td>
                            @endif
                        @elseif($fieldValue["mode"]=="prefer_external")
                            @if( $fieldValue["source"] == 'external')
                                <td style="width:40%">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["external"]])
                                    <span class="mm-default">External</span>
                                </td>
                                <td style="width:40%">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["local"]])
                                    <span class="mm-default">Local</span>
                                </td>
                            @endif
                            @if( $fieldValue["source"] == 'local')
                                <td colspan="2">
                                    @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["local"]])
                                    <span class="mm-default">Local</span>
                                </td>
                            @endif
                        @elseif($fieldValue["mode"]=="only_local")
                            <td colspan="2">
                                @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["local"]])
                            </td>
                        @elseif($fieldValue["mode"]=="only_external")
                            <td colspan="2">
                                @include('showField.field',["field"=>$fieldValue['field'],"value"=>$fieldValue["external"]])
                            </td>
                        @endif


                    </tr>
                @endforeach
                @foreach( $links as $link )
                    <tr>
                        <th>{{$link["title"]}}:</th>
                        <td colspan="2">
                            @foreach( $link["records"] as $subrecord)
                                <a
                                        href="@url($subrecord["record"])"
                                        data-rid="{{$subrecord["record"]->sid}}"
                                        class="mm-record-stub mm-record-entity mm-record-{{$subrecord["record"]->sid}}"
                                >
                                    @title( $subrecord["record"])
                                </a>
                            @endforeach
                            @can('edit',$record)
                                <a class="mm-button" href="{{$link["createLink"]}}">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </a>
                            @endcan
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
