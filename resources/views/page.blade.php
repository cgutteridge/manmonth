<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>

    <link href="/bootstrap.min.css" rel="stylesheet">
    <link href="/manmonth.css" rel="stylesheet">
    <script src=/jquery.min.js"></script>
    <script src="/bootstrap.min.js"></script>

</head>
<body>
<div class="container">
    <div class="content">
        <h1 class="title">@yield('title')</h1>
        @yield('content')
    </div>
</div>
</body>
</html>
