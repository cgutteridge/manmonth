<header>
    <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
        <div class="mm-status-banner"></div>
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand navbar-ident" href="/">ManMonth</a>
                @if( isset($nav["title"]) )
                    @if(isset($nav["title"]["url"]))
                        <a class="navbar-brand" href="{{$nav["title"]["url"]}}">{{$nav["title"]["label"]}}</a>
                    @else
                        <span class="navbar-brand">{{$nav["title"]["label"]}}</span>
                    @endif
                @endif
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    @if( array_key_exists('menus',$nav))
                        @foreach( $nav['menus'] as $menu )
                            @include( 'nav.menu', [ 'menu'=>$menu ])
                        @endforeach
                    @endif
                </ul>
                @if( array_key_exists('usermenu',$nav))
                    <ul class="nav navbar-nav navbar-right">
                        @include( 'nav.menu', [ 'menu'=>$nav['usermenu'] ])
                    </ul>
                @endif
                @if( array_key_exists('sitestatus',$nav))
                    <div class="navbar-text navbar-right mm-site-status-text">
                        {{$nav['sitestatus']}}
                    </div>
                @endif
            </div>
        </div><!-- /.container-fluid -->
    </nav>
    @if( array_key_exists("side",$nav))
        <div class="mm-sidestatus mm_sidestatus_{{$nav['side']['status']}}">
            {{ $nav['side']['label'] }}
        </div>
    @endif
</header>
