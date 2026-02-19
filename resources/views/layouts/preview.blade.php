<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Sitio Web</title>

    <!-- Maquimport CSS -->
    <link rel="stylesheet" href="{{ asset('css/bundle.css') }}">
    <link rel="stylesheet" href="{{ asset('css/maquimport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/magiczoom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <!-- Google Fonts - Manrope -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;500;700;800&display=swap" rel="stylesheet">

    @stack('styles')
</head>

<body>
    @yield('content')

    <!-- Maquimport JS -->
    <script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bundle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/main.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/magiczoom.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fancybox.min.js') }}"></script>

    @stack('scripts')
</body>
</html>