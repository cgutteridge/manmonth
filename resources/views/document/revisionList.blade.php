@inject('linkMaker','App\Http\LinkMaker')
<ul>
    @foreach( $document->revisions->reverse() as $revision )
        @if( $revision->status == $showStatus)
            <li>
                <a href="{{ $linkMaker->link( $revision ) }}">
                    {{date("D jS F, Y. g:ia",strtotime($revision->created_at))}}
                </a>
            </li>
        @endif
    @endforeach
</ul>
