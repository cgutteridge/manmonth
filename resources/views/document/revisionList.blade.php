<ul>
    @foreach( $document->revisions->reverse() as $revision )
        @if( $revision->status == $showStatus)
            <li>
                <a href="@url( $revision )">
                    @datetime( $revision->created_at )
                </a>
            </li>
        @endif
    @endforeach
</ul>
