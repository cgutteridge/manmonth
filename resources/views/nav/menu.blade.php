@if(array_key_exists('items',$menu))
    <li class="dropdown">
        <a tabindex="0" data-toggle="dropdown" data-submenu>
            {{$menu["label"]}}<span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            @foreach( $menu['items'] as $item )
                @include( 'nav.item', [ "item"=>$item] )
            @endforeach
        </ul>
    </li>
@else
    @include( 'nav.item', [ "item"=>$menu ] )
@endif
