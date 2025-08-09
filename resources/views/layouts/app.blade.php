<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/mobile-responsive.css', 'resources/css/custom-dropdown.css', 'resources/css/kanban-mobile.css', 'resources/js/app.js', 'resources/js/mobile-adaptive.js', 'resources/js/kanban-mobile.js', 'resources/js/column-mobile-optimizer.js'])
</head>
<body>
    <div id="app">
      
        <main class="">
            @yield('content')
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Дополнительная отладка Bootstrap -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bootstrap загружен:', typeof bootstrap !== 'undefined');
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);
            }
        });
    </script>
</body>
</html>
