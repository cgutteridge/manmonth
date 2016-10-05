@inject('linkMaker','App\Http\LinkMaker' )
<div class="panel panel-info mm-record-block">
    <div class="panel-heading">
        <a href="{{ $linkMaker->link($record) }}" title="focus">
            <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
            {{ $record->title() }}
        </a>
        <a href="{{ $linkMaker->edit($record, $editParams) }}" class="pull-right" title="edit"><span
                    class="glyphicon glyphicon-edit"
                    aria-hidden="true"></span></a>
        <i class="pull-right" style="margin-right:1em">{{ $record->recordType->name }} </i>
    </div>
    <table class="table">
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
                            <th>{{  $link->linkType->title() }}:</th>
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

