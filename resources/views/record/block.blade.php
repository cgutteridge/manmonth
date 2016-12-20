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
                @foreach( $data as $field )
                    <tr style='cursor:pointer;'
                        data-toggle="tooltip"
                        title="Click to view"
                        onclick="document.location.href = '@url($record)';">
                        <th>{{$field["title"]}}:</th>
                        @if( $field["source"] == 'default')
                            <td colspan="2">{{$field["default"]}} <span class="mm-default">Default</span></td>
                        @elseif( $field["source"] == 'none')
                            <td colspan="2"><span class="mm-default">NULL</span></td>
                        @elseif($field["mode"]=="prefer_local")
                            @if( $field["source"] == 'local')
                                <td style="width:40%">{{$field["local"]}}<span class="mm-default">Local</span></td>
                                <td style="width:40%">{{$field["external"]}}<span class="mm-default">External</span>
                                </td>
                            @endif
                            @if( $field["source"] == 'external')
                                <td colspan="2">{{$field["external"]}}<span class="mm-default">External</span></td>
                            @endif
                        @elseif($field["mode"]=="prefer_external")
                            @if( $field["source"] == 'external')
                                <td style="width:40%">{{$field["external"]}} <span class="mm-default">External</span>
                                </td>
                                <td style="width:40%">{{$field["local"]}} <span class="mm-default">Local</span></td>
                            @endif
                            @if( $field["source"] == 'local')
                                <td colspan="2">{{$field["local"]}} <span class="mm-default">Local</span></td>
                            @endif
                        @elseif($field["mode"]=="only_local")
                            <td colspan="2">{{$field["local"]}}</td>
                        @elseif($field["mode"]=="only_external")
                            <td colspan="2">{{$field["external"]}}</td>
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
