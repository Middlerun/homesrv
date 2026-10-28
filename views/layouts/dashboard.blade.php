<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeSrv Dashboard</title>
    <link rel="stylesheet" href="/assets/css/app.css?v={{ filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/app.css') }}">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="/assets/js/htmx.min.js" defer></script>
</head>
<body class="dashboard">
    @yield('content')
</body>
</html>
