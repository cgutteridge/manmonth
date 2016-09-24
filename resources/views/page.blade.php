<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>

    <link href="/bootstrap.min.css" rel="stylesheet">
    <link href="/manmonth.css" rel="stylesheet">
    <script src="/jquery.min.js"></script>
    <script src="/bootstrap.min.js"></script>

</head>
<body>
@include("nav")
<div class="container">
    <div class="content">
        @hasSection ('context')
            <div class="row">
                <div class="col-md-8">
                    <h1 class="title">@yield('title')</h1>
                </div>
                <div class="col-md-4">
                    @yield('context')
                </div>
            </div>
        @else
            <h1 class="title">@yield('title')</h1>
        @endif
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
</body>
</html>
