<ul>
    @foreach($revisions as $revision)
        <tr style="{{ (( $revision['latest_published'] || $revision['published'] ) ? "font-weight:bold;" : "") }}">
            <td>
                <a href="{{ $revision["url"] }}">
                    @datetime( $revision['created_at'] )
                </a>
            </td>
            <td>
                @if( $revision['latest_published'])
                    Latest published
                @elseif( $revision['published'])
                    Published
                    @else
                    {{ ucfirst( $revision['status'] ) }}
                @endif
            </td>
            <td>
                @if( !empty($revision['user']))
                    {{ $revision['user'] }}
                @endif
            </td>
            <td>
                @if( !empty($revision['comment']))
                    {{ $revision['comment'] }}
                @endif
            </td>
        </tr>
    @endforeach
</ul>