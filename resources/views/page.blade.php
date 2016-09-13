<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>

    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="/manmonth.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

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
