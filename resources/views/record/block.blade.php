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
                <th>{{$field->title()}}:</th>
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
            @foreach( $record->forwardLinks as $link )
                @if( !array_key_exists($link->objectRecord->id,$seen))
                    @if( $followLink == 'all' || (isset($link->linkType->range_max) && $link->linkType->range_max==1 ))
                        <tr>
                            <th>@title($link->linkType):</th>
                            <td>
                                @include( 'record.block', [
                                    'record'=>$link->objectRecord,
                                    'followLink'=>'single',
                                    'editParams'=>$editParams,
                                    'seen'=>array_replace($seen,[$record->id=>1])
                                ])
                            </td>
                        </tr>
                    @endif
                @endif
            @endforeach
            @foreach( $record->backLinks as $link )
                @if( !array_key_exists($link->subjectRecord->id,$seen))
                    @if( $followLink == 'all' || (isset($link->linkType->domain_max) && $link->linkType->domain_max==1 ))
                        <tr>
                            <th>{{ $link->linkType->inverseTitle() }}:</th>
                            <td>
                                @include( 'record.block', ['record'=>$link->subjectRecord,'followLink'=>'single', 'seen'=>array_replace($seen,[$record->id=>1])])
                            </td>
                        </tr>
                    @endif
                @endif
            @endforeach
        @endif
    </table>
</div>

