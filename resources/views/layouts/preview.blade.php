<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Preview')</title>
    <!-- Google Fonts -->
    <link rel="icon" href="{{ asset('images/icon.png') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    @yield('content')

    @stack('scripts')
</body>
</html>