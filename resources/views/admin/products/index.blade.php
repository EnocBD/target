{{-- resources/views/admin/products/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Productos')
@section('page-title', 'Productos')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Productos</span></li>
@endpush

@section('content')
    <div x-data="{ showDeleteModal: false, productId: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Productos</h1>
                <p class="text-gray-600 mt-1">Administra los productos del sitio</p>
            </div>
            @can('create-products')
                <a href="{{ route('admin.products.create') }}"
                   class="btn btn-primary group shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                    Nuevo Producto
                </a>
            @endcan
        </div>

        <!-- Search -->
        <div class="mb-6">
            <form action="{{ route('admin.products.index') }}" method="GET" class="flex gap-3">
                <div class="flex-1">
                    <input type="text" name="search" class="form-input" placeholder="Buscar por nombre, marca o categoría..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Limpiar
                    </a>
                @endif
            </form>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($products->count() > 0)
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Media</th>
                                <th>Estado</th>
                                <th>Orden</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg overflow-hidden mr-4">
                                            @if($product->mainImage)
                                                <img src="{{ $product->mainImage->thumbnail_url ?? $product->mainImage->file_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                    <i class="fas fa-box text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $product->excerpt ? Str::limit($product->excerpt, 80) : Str::limit(strip_tags($product->description), 50) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-2">
                                        <div class="text-sm text-gray-600">
                                            @if($product->media->count() > 0)
                                                <span class="flex items-center">
                                                    <i class="fas fa-images text-blue-500 mr-1"></i>
                                                    {{ $product->media->count() }} archivo(s)
                                                </span>
                                                @if($product->images->count() > 0)
                                                    <span class="ml-2 text-green-500">
                                                        <i class="fas fa-image"></i> {{ $product->images->count() }} img(s)
                                                    </span>
                                                @endif
                                                @if($product->videos->count() > 0)
                                                    <span class="ml-2 text-purple-500">
                                                        <i class="fas fa-video"></i> {{ $product->videos->count() }} vid(s)
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">
                                                    <i class="fas fa-image text-gray-300 mr-1"></i>
                                                    Sin media
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($product->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-warning">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-gray-600">{{ $product->order }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.products.show', $product) }}"
                                           class="action-btn action-btn-view"
                                           title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('edit-products')
                                            <a href="{{ route('admin.products.edit', $product) }}"
                                               class="action-btn action-btn-edit"
                                               title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endcan
                                        @can('delete-products')
                                            <button @click="showDeleteModal = true; productId = {{ $product->id }}"
                                                    class="action-btn action-btn-delete"
                                                    title="Eliminar">
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
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $products->links('pagination::tailwind') }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full">
                            <i class="fas fa-box-open text-2xl text-gray-400"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay productos</h3>
                    <p class="text-gray-500 mb-6">Comienza agregando tu primer producto.</p>
                    @can('create-products')
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Agregar Producto
                        </a>
                    @endcan
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

                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">¿Eliminar producto?</h3>
                    <p class="text-gray-500 text-center mb-6">Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar este producto?</p>

                    <div class="flex space-x-3">
                        <form action="{{ route('admin.products.destroy', ':productId') }}" method="POST" x-ref="deleteForm">
                            @csrf
                            @method('DELETE')
                        </form>

                        <button @click="showDeleteModal = false"
                                class="flex-1 btn btn-secondary">
                            Cancelar
                        </button>

                        <button @click="$refs.deleteForm.submit(); showDeleteModal = false"
                                class="flex-1 btn btn-danger">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection