@inject("navMaker","App\Http\NavigationMaker")
        <!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <link href="/manmonth.css" rel="stylesheet">
    <link href="/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap-submenu.min.css" rel="stylesheet">
    <script src="/jquery.min.js"></script>
    <script src="/bootstrap.min.js"></script>
    <script src="/bootstrap-submenu.min.js" defer></script>
    <script src="/hover.js"></script>
    <script src="/manmonth.js"></script>
</head>
<body class="mm-site-status-{{\App::environment()}}
        @if( isset($nav) && isset($nav['side']) )
        mm-doc-status-{{$nav['side']['status']}}
        @endif
        "
>
@include( 'header', [ "nav"=>(isset($nav)?$nav:$navMaker->defaultNavigation())])

<div class="container" style="margin-top: 50px">
    <div class="content">
        <h1 class="title">@yield('title')</h1>

        @if ( (isset($errors) && count($errors) )||(isset($renderErrors) && count($renderErrors) ))
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    @if(!empty($renderErrors))
                        @foreach ($renderErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    @endif
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

@include( 'footer', [ "nav"=>(isset($nav)?$nav:$navMaker->defaultNavigation())])

</body>
</html>
