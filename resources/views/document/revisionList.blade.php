<ul>
    @foreach($revisions as $revision)
        <li>
            <a href="{{ $revision["url"] }}">
                @if( $revision['latest_published'])
                    <strong>@datetime( $revision['created_at'] ) - latest published</strong>
                @elseif( $revision['published'])
                    <strong>@datetime( $revision['created_at'] ) - published</strong>
                @else
                    @datetime( $revision['created_at'] )
                @endif
            </a>
        </li>
    @endforeach
</ul>