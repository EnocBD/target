<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Target Eyewear - Tu Visión, Nuestra Pasión')</title>
    <!-- Global App Configuration -->
    <script>
        window.AppConfig = {
            baseUrl: '{{ url('/') }}',
            apiUrl: '{{ url('/cart/api') }}',
            routes: {
                cart: '{{ route('cart.index') }}',
                checkout: '{{ route('cart.checkout') }}',
                products: '{{ url('/productos') }}'
            }
        };
    </script>
    <!-- Google Fonts -->
    <link rel="icon" href="{{ asset('images/icon.png') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .btn-primary {
  transition: all .25s ease;
}

.btn-primary:hover {
  background-color: #098CC0 !important;
  border-color: #098CC0 !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,.15);
}
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Target Eyewear" height="50">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('nosotros') ? 'active' : '' }}" href="{{ url('/nosotros') }}">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('productos*') ? 'active' : '' }}" href="{{ url('/productos') }}">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('contacto') ? 'active' : '' }}" href="{{ url('/contacto') }}">Contacto</a>
                    </li>
                </ul>
                <form class="d-flex me-3" action="{{ url('/productos') }}" method="GET">
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control" name="q" placeholder="Buscar productos..." value="{{ request('q') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('cart.index') }}" class="btn btn-primary position-relative">
                        <i class="fas fa-cart-shopping"></i>
                        <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ \LukePOLO\LaraCart\Facades\LaraCart::count() }}
                        </span>
                    </a>
                    @if(auth()->check() && auth()->user()->can('view-admin'))
                    <a href="{{ url('/admin') }}" class="btn btn-outline-primary">
                        <i class="fas fa-gear"></i> Admin
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="bg-primary text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Target Eyewear" height="50" class="mb-3" style="filter: brightness(0) invert(1);">
                    <p class="text-white-50">Tu visión, nuestra pasión. Lentes de alta calidad para tu estilo de vida.</p>
                </div>
                <div class="col-md-auto mb-4">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        @foreach($footerMenu ?? [] as $item)
                            <li class="mb-2"><a href="{{ url($item['slug']) }}" class="text-white text-decoration-none">{{ $item['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-auto mb-4">
                    <h5>Síguenos</h5>
                    <div class="d-flex flex-column gap-3">
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-facebook-f me-2"></i> Facebook
                        </a>
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-instagram me-2"></i> Instagram
                        </a>
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            <hr class="border-white-50">
            <div class="row">
                <div class="col-md-6 text-left">
                    <p class="mb-0 text-white-50">&copy; {{ date('Y') }} Target Eyewear. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-white-50">
                        Desarrollado con amor en 
                    <a href="https://primogenito.com.py" target="_new"
                       style="color: rgba(255,255,255,.5); text-decoration:none; transition:color .25s ease;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">
                        ❤ Primogenito
                    </a>
                </p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
