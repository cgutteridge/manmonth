<div class="panel panel-info mm-record-block">
    <div class="panel-heading">

        <a href="@url($record)" data-toggle="tooltip" title="Focus on this @title($record->recordType)">
            <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
            @title($record)
        </a>
        <a href="@url($record, 'edit', $editParams)" class="pull-right" data-toggle="tooltip"
           title="Edit this @title($record->recordType)">
             <span
                     class="glyphicon glyphicon-edit"
                     aria-hidden="true"></span>
        </a>
        <i class="pull-right" style="font-size:80%;padding-top:0.2em;margin-right:2em">[@title($record->recordType)]</i>
    </div>
    <table class="table mm-table">
        @foreach( $record->recordType->fields() as $field )
            <tr>
                <th>@title($field):</th>
                <td style="width:100%">
                    @if( array_key_exists( $field->data["name"], $record->data ) )
                        {{$record->data[$field->data["name"]]}}
                    @else
                        <span class="mm-null">NULL</span>
                    @endif
                </td>
            </tr>
        @endforeach

        @if( $followLink != 'none' )
            @foreach( $record->recordType->forwardLinkTypes as $linkType )
                <tr>
                    <th>@title($linkType):</th>
                    <td>
                        @include( 'record.linkedRecords', [
                            "seen"=>array_replace($seen,[$record->id=>1]),
                            "editParams"=>$editParams,
                            "followLink"=>$followLink,
                            "min"=>$linkType->range_min,
                            "max"=>$linkType->range_max,
                            "records"=>$record->forwardLinkedRecords($linkType)])
                    </td>
                </tr>
            @endforeach
            @foreach( $record->recordType->backLinkTypes as $linkType )
                <tr>
                    <th>@title($linkType,'inverse'):</th>
                    <td>
                        @include( 'record.linkedRecords', [
                            "seen"=>array_replace($seen,[$record->id=>1]),
                            "editParams"=>$editParams,
                            "followLink"=>$followLink,
                            "min"=>$linkType->domain_min,
                            "max"=>$linkType->domain_max,
                            "records"=>$record->backLinkedRecords($linkType)])
                    </td>
                </tr>
            @endforeach
        @endif

    </table>
</div>

