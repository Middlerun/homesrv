<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HomeSrv')</title>
    <link rel="stylesheet" href="/assets/css/app.css?v={{ filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/app.css') }}">
    <script src="/assets/js/htmx.min.js" defer></script>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand-group">
            <a href="/" class="nav-brand">HomeSrv</a>
            @if(!empty($_SESSION['user_name']))
                <span class="nav-user">{{ $_SESSION['user_name'] }}</span>
            @endif
        </div>
        <div class="nav-links">
            <a href="/tasks/">Tasks</a>
            <a href="/events/">Events</a>
            <a href="/dashboard/">Dashboard</a>
            <a href="/settings/">Settings</a>
        </div>
    </nav>
    <main class="container">
        @yield('content')
    </main>
</body>
</html>
