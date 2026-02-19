<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Panel de Administración</title>
    <link rel="icon" href="{{ asset('images/icon.png') }}">

    @stack('styles')
    <!-- Vite CSS -->
    @vite(['resources/css/admin.css'])
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">
<!-- Mobile overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden" 
         @click="sidebarOpen = false"
         style="display: none;"></div>

    <!-- Sidebar -->
    <nav class="bg-gradient-to-b from-primary to-primary-700 text-white h-screen fixed left-0 top-0 overflow-y-auto w-64 transform transition-transform duration-300 lg:translate-x-0 z-30"
         :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">
        
        <!-- Brand -->
        <div class="p-6 border-b border-primary-600">
            <a href="{{ url('/') }}" class="block">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 mx-auto invert brightness-0">
            </a>
        </div>

        <!-- Navigation -->
        <div class="p-4">
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                    <i class="fas fa-gauge-high w-5 text-center mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Content Management Section -->
                <div class="px-4 py-2 text-xs font-semibold text-primary-300 uppercase tracking-wider">Gestión de Contenido</div>

                @can('view-pages')
                    <a href="{{ route('admin.pages.index') }}"
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.pages.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-file-lines w-5 text-center mr-3"></i>
                        <span>Páginas</span>
                    </a>

                    <a href="{{ route('admin.blocks.index') }}"
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.blocks.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-cubes w-5 text-center mr-3"></i>
                        <span>Bloques</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.products.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-box w-5 text-center mr-3"></i>
                        <span>Productos</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}"
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.categories.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-tags w-5 text-center mr-3"></i>
                        <span>Categorías</span>
                    </a>

                @endcan

                <!-- User Management Section -->
                @can('view-users')
                    <div class="px-4 py-2 text-xs font-semibold text-primary-300 uppercase tracking-wider">Administración</div>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.users.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-user-cog w-5 text-center mr-3"></i>
                        <span>Usuarios</span>
                    </a>

                    <a href="{{ route('admin.forms.index') }}" 
                       class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.forms.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                        <i class="fas fa-envelope w-5 text-center mr-3"></i>
                        <span>Formularios</span>
                    </a>

                    @can('view-settings')
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 {{ request()->routeIs('admin.settings.*') ? 'bg-primary-600 border-r-4 border-primary-300' : '' }}">
                            <i class="fas fa-gear w-5 text-center mr-3"></i>
                            <span>Configuración</span>
                        </a>
                    @endcan
                @endcan

                <!-- Quick Actions -->
                <div class="px-4 py-2 text-xs font-semibold text-primary-300 uppercase tracking-wider">Acciones Rápidas</div>

                <a href="{{ url('/') }}" target="_blank" 
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1">
                    <i class="fas fa-up-right-from-square w-5 text-center mr-3"></i>
                    <span>Ver Sitio Web</span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="flex items-center px-4 py-3 text-white hover:bg-primary-600 transition-colors duration-200 rounded-lg mb-1 w-full text-left">
                        <i class="fas fa-right-from-bracket w-5 text-center mr-3"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </nav>

    <!-- Main content -->
    <main class="ml-0 lg:ml-64 min-h-screen bg-gray-50">
        <!-- Top Navigation -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 shadow-sm flex justify-between items-center">
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 mr-4">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    <nav class="mt-1">
                        <ol class="flex space-x-2 text-sm text-gray-500">
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-house"></i>
                                </a>
                            </li>
                            @stack('breadcrumbs')
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- User dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-primary">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-medium mr-2">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden md:inline text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down ml-1 text-gray-500"></i>
                    </button>
                    
                    <div x-show="userMenuOpen" 
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="dropdown-menu"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item w-full text-left">
                                <i class="fas fa-right-from-bracket mr-2"></i>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="p-6">
            <!-- Alerts -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform translate-y-2"
                     class="alert alert-success flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-800 hover:text-green-900">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform translate-y-2"
                     class="alert alert-danger flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                    <button @click="show = false" class="text-red-800 hover:text-red-900">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ show: true }" x-show="show" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform translate-y-2"
                     class="alert alert-danger">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Por favor corrige los siguientes errores:</strong>
                            </div>
                            <ul class="mt-2 ml-6 list-disc">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-red-800 hover:text-red-900 ml-4">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </main>

<!-- Vite JS -->
@vite(['resources/js/admin.js'])
@stack('scripts')
</body>
</html>
