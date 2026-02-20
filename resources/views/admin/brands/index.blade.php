@extends('layouts.admin')

@section('title', 'Marcas')
@section('page-title', 'Marcas')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Marcas</span></li>
@endpush

@section('content')
    <div x-data="{ showDeleteModal: false, brandId: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Marcas</h1>
                <p class="text-gray-600 mt-1">Administra las marcas de productos</p>
            </div>
            @can('create-brands')
                <a href="{{ route('admin.brands.create') }}"
                   class="btn btn-primary group shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                    Nueva Marca
                </a>
            @endcan
        </div>

        <!-- Search -->
        <div class="mb-6">
            <form action="{{ route('admin.brands.index') }}" method="GET" class="flex gap-3">
                <div class="flex-1">
                    <input type="text" name="search" class="form-input" placeholder="Buscar por nombre..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Limpiar
                    </a>
                @endif
            </form>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($brands->count() > 0)
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Marca</th>
                                <th>Estado</th>
                                <th>Orden</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($brands as $brand)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900">{{ $brand->name }}</div>
                                    @if($brand->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($brand->description, 80) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($brand->is_active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-pause mr-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-gray-600">{{ $brand->sort_order }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        @can('edit-brands')
                                            <a href="{{ route('admin.brands.edit', $brand) }}"
                                               class="action-btn action-btn-edit"
                                               title="Editar marca">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endcan

                                        @can('delete-brands')
                                            <button @click="showDeleteModal = true; brandId = {{ $brand->id }}"
                                                    class="action-btn action-btn-delete"
                                                    title="Eliminar marca">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $brands->appends(['search' => request('search')])->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-trademark text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay marcas</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request('search'))
                            No se encontraron resultados para "{{ request('search') }}"
                        @else
                            Comienza creando una nueva marca
                        @endif
                    </p>
                    @if(!request('search') && auth()->user()->can('create-brands'))
                        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Crear Primera Marca
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             style="display: none;">

            <div @click.away="showDeleteModal = false" class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">¿Eliminar marca?</h3>
                    <p class="text-gray-500 text-center mb-6">Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar esta marca?</p>

                    <div class="flex space-x-3">
                        <form action="/admin/brands/:brandId" method="POST" x-ref="deleteForm">
                            @csrf
                            @method('DELETE')
                        </form>

                        <button @click="showDeleteModal = false"
                                class="flex-1 btn btn-secondary">
                            Cancelar
                        </button>

                        <button @click="if (brandId) { $refs.deleteForm.action = $refs.deleteForm.action.replace(':brandId', brandId); $refs.deleteForm.submit(); }"
                                class="flex-1 btn btn-danger">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
