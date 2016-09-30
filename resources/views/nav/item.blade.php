@if(array_key_exists('items',$item))
    <li class="dropdown-submenu">
        <a tabindex="0">{{$item['label']}}</a>
        <ul class="dropdown-menu">
            @foreach( $item['items'] as $subitem )
                @include( 'nav.item', [ "item"=>$subitem ] )
            @endforeach
        </ul>
    </li>
@elseif( array_key_exists( 'disabled',$item ) && $item['disabled'] )
    <li class="disabled"><a tabindex="-1">{{$item['label']}}</a></li>
@elseif( array_key_exists( 'href', $item ))
    <li><a tabindex="0" href="{{$item['href']}}">{{$item['label']}}</a></li>
@else
    <li><a tabindex="0">{{$item['label']}}</a></li>
@endif
