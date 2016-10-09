<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>

    <link href="/bootstrap.min.css" rel="stylesheet">
    <link href="/manmonth.css" rel="stylesheet">
    <link href="/bootstrap-submenu.min.css" rel="stylesheet">
    <script src="/jquery.min.js"></script>
    <script src="/bootstrap.min.js"></script>
    <script src="/bootstrap-submenu.min.js" defer></script>
    <script src="/hover.js"></script>
    <script>
        $(document).ready(function () {
            $('[data-submenu]').submenupicker();
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top navbar-inverse">

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
            <a class="navbar-brand" href="/">[MM]</a>
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
            @include( 'nav.userMenu')
        </div>
    </div><!-- /.container-fluid -->
</nav>
@if( array_key_exists("side",$nav))
    <div class="mm_sidestatus mm_sidestatus_{{$nav['side']['status']}}">
        {{ $nav['side']['label'] }}
    </div>
@endif
<div class="container" style="margin-top: 50px">
    <div class="content">
        <h1 class="title">@yield('title')</h1>

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        @yield('content')
    </div>
</div>
<footer class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">[MM]</a>
            <a class="navbar-brand">
                ManMonth dev version. &copy;2016 University of Southampton.
            </a>
        </div>
    </div>
</footer>
</body>
</html>
