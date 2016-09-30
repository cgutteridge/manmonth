@inject('linkMaker','App\Http\Controllers\LinkMaker')
<div class="panel panel-info mm-record-block">
    <div class="panel-heading">
        <a href="{{ $linkMaker->link($record) }}" title="focus"><span class="glyphicon glyphicon-eye-open"
                                                                      aria-hidden="true"></span></a>
        <a href="{{ $linkMaker->edit($record) }}" class="pull-right" title="edit"><span class="glyphicon glyphicon-edit"
                                                                                        aria-hidden="true"></span></a>
        <i class="pull-right" style="margin-right:1em">{{ $record->recordType->name }} </i>
        {{ $record->title() }}
    </div>
    <table class="table">
        @include( 'dataTable', ['data'=>$record->data ])
        @if( $followLink != 'none' )
            @foreach( $record->forwardLinks as $link )
                @if( !array_key_exists($link->objectRecord->id,$seen))
                    @if( $followLink == 'all' || @$link->linkType->data["domain_max"]==1 )
                        <tr>
                            <th>{{ preg_replace( '/_/',' ',$link->linkType->name) }}:</th>
                            <td>
                                @include( 'record.block', ['record'=>$link->objectRecord,'followLink'=>'single', 'seen'=>array_replace($seen,[$record->id=>1])])
                            </td>
                        </tr>
                    @endif
                @endif
            @endforeach
            @foreach( $record->backLinks as $link )
                @if( !array_key_exists($link->subjectRecord->id,$seen))
                    @if( $followLink == 'all' || @$link->linkType->data["range_max"]==1 )
                        <tr>
                            <th>Is {{ preg_replace( '/_/',' ',$link->linkType->name) }} of:</th>
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

