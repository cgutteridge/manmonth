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
<div class="container">
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
</body>
</html>
