<ul>
    @foreach( $document->revisions->reverse() as $revision )
        @if( $revision->status == $showStatus)
            <li>
                <a href="@url( $revision )">
                    @if( $revision->published )
                        <strong>
                            @datetime( $revision->created_at )
                            - published
                        </strong>
                    @else
                        @datetime( $revision->created_at )
                    @endif
                </a>
            </li>
        @endif
    @endforeach
</ul>
