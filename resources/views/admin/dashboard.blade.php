{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Páginas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 group hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Total Páginas</p>
                    <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats['total_pages'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-file-lines"></i>
                </div>
            </div>
        </div>

        <!-- Páginas Activas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 group hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Páginas Activas</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-2">{{ $stats['active_pages'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </div>

        <!-- Total Bloques -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 group hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Total Bloques</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats['total_blocks'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>

        <!-- Total Usuarios -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 group hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Total Usuarios</p>
                    <p class="text-2xl font-bold text-cyan-600 mt-2">{{ $stats['total_users'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bienvenido al Panel de Administración</h3>
        </div>
        <div class="card-body">
            <div class="text-center py-8">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary to-primary-700 rounded-full mb-4">
                        <i class="fas fa-building text-2xl text-white"></i>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Gestión de Contenido</h2>
                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    Administra las páginas y bloques de contenido del sitio web.
                    Utiliza las herramientas disponibles para mantener tu sitio actualizado y optimizado.
                </p>
                
                @can('view-pages')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-md mx-auto">
                        <a href="{{ route('admin.pages.index') }}" 
                           class="btn btn-primary group">
                            <i class="fas fa-file-lines mr-2 group-hover:scale-110 transition-transform"></i>
                            Gestionar Páginas
                        </a>
                        
                        <a href="{{ route('admin.blocks.index') }}" 
                           class="btn btn-secondary group border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-cubes mr-2 group-hover:scale-110 transition-transform"></i>
                            Gestionar Bloques
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>

@endsection
