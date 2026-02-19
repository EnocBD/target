{{-- resources/views/admin/pages/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Páginas')
@section('page-title', 'Páginas')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Páginas</span></li>
@endpush

@section('content')
    <div x-data="{ showDeleteModal: false, pageId: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Páginas</h1>
                <p class="text-gray-600 mt-1">Administra las páginas de tu sitio web</p>
            </div>
            @can('create-pages')
                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary group shadow-lg flex items-center">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                    Nueva Página
                </a>
            @endcan
        </div>

        @if($pages->count() > 0)
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="pages-grid">
                @foreach($pages as $page)
                    <div data-id="{{ $page->id }}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-primary to-primary-700 rounded-lg flex items-center justify-center text-white">
                                        <i class="fas fa-file-lines text-lg"></i>
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 text-lg truncate">{{ $page->title }}</h3>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <code class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-800 font-mono">
                                                {{ $page->slug }}
                                            </code>
                                            @if($page->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check w-2.5 h-2.5 mr-1"></i>
                                                    Activa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-xmark w-2.5 h-2.5 mr-1"></i>
                                                    Inactiva
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center ml-2">
                                    <i class="fas fa-grip-vertical text-gray-400 cursor-move"></i>
                                </div>
                            </div>

                            @if($page->meta_description)
                                <p class="text-sm text-gray-600 mt-3 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    {{ Str::limit($page->meta_description, 120) }}
                                </p>
                            @endif

                            <!-- Page Info -->
                            <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar-days mr-1"></i>
                                        {{ $page->created_at->format('d/m/Y') }}
                                    </span>
                                    @if($page->menu_position)
                                        <span class="flex items-center">
                                            <i class="fas fa-bars mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $page->menu_position)) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="flex items-center">
                                    <i class="fas fa-cubes mr-1"></i>
                                    {{ $page->blocks->count() }} bloques
                                </span>
                            </div>
                        </div>

                        <!-- Blocks Accordion -->
                        <div x-data="{ expanded: false }" class="border-b border-gray-100">
                            <button @click="expanded = !expanded"
                                    class="w-full px-6 py-3 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <span class="text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-cubes mr-2 text-gray-400"></i>
                                    Bloques ({{ $page->blocks->count() }})
                                </span>
                                <i class="fas fa-chevron-down transform transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="expanded" x-collapse class="px-6 pb-4">
                                @if($page->blocks->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($page->blocks->sortBy('position') as $block)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <i class="fas fa-puzzle-piece text-blue-600 text-sm"></i>
                                                    </div>
                                                    <div class="ml-3 flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">
                                                            {{ $block->getDataValue('title') ?: 'Bloque #' . $block->id }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            @php $layoutConfig = config("blocks.layouts.{$block->block_type}", []); @endphp
                                                            {{ $layoutConfig['name'] ?? ucfirst($block->block_type) }} • Posición {{ $block->sort_order }}
                                                        </p>
                                                    </div>
                                                </div>
                                                @can('edit-pages')
                                                    <a href="{{ route('admin.blocks.edit', $block) }}"
                                                       class="ml-2 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                        Editar
                                                    </a>
                                                @endcan
                                            </div>
                                        @endforeach
                                    </div>
                                    @can('create-pages')
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <a href="{{ route('admin.blocks.create', ['page_id' => $page->id]) }}"
                                               class="text-xs text-primary hover:text-primary-700 font-medium flex items-center">
                                                <i class="fas fa-plus mr-1"></i>
                                                Agregar bloque
                                            </a>
                                        </div>
                                    @endcan
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500 mb-2">No hay bloques en esta página</p>
                                        @can('create-pages')
                                            <a href="{{ route('admin.blocks.create', ['page_id' => $page->id]) }}"
                                               class="text-xs text-primary hover:text-primary-700 font-medium flex items-center justify-center">
                                                <i class="fas fa-plus mr-1"></i>
                                                Crear primer bloque
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                            <div class="flex space-x-3">
                                @can('edit-pages')
                                    <a href="{{ route('admin.pages.edit', $page) }}"
                                       class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                                        <i class="fas fa-pen-to-square mr-1"></i>
                                        Editar página
                                    </a>
                                @endcan

                                @can('delete-pages')
                                    <button type="button"
                                            class="text-sm font-medium text-red-600 hover:text-red-800 flex items-center"
                                            @click="pageId = {{ $page->id }}; showDeleteModal = true">
                                        <i class="fas fa-trash-can mr-1"></i>
                                        Eliminar
                                    </button>
                                @endcan
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ $page->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Info -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Mostrando {{ $pages->count() }} páginas en total
                </p>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="empty-state text-center py-16">
                    <i class="fas fa-file-lines text-gray-300 text-6xl mb-4"></i>
                    <h3 class="empty-state-title text-xl font-semibold mb-2">No hay páginas creadas</h3>
                    <p class="empty-state-description text-gray-500 mb-4">
                        Comienza a construir tu sitio web creando tu primera página. Las páginas te permiten organizar el contenido de tu sitio.
                    </p>
                    @can('create-pages')
                        <a href="{{ route('admin.pages.create') }}"
                           class="btn btn-primary group shadow-lg flex items-center justify-center mx-auto">
                            <i class="fas fa-plus mr-2"></i> Crear Primera Página
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        @can('delete-pages')
            <!-- Delete Modal -->
            <div x-show="showDeleteModal" x-cloak
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="showDeleteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                        <div class="bg-white px-6 pt-6 pb-4 flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900">Confirmar eliminación</h3>
                                <p class="text-sm text-gray-600 mt-2">
                                    ¿Estás seguro de que deseas eliminar esta página? Esta acción no se puede deshacer y se perderán todos los datos asociados.
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button @click="showDeleteModal = false" class="btn btn-secondary w-full sm:w-auto">Cancelar</button>
                            <form :action="'{{ route('admin.pages.destroy', ':id') }}'.replace(':id', pageId)" method="POST" class="w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-full sm:w-auto flex items-center justify-center">
                                    <i class="fas fa-trash-can mr-2"></i> Eliminar página
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('styles')
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script>
        const grid = document.getElementById('pages-grid');
        if (grid) {
            new Sortable(grid, {
                animation: 150,
                handle: '.cursor-move',
                onEnd: evt => {
                    const movedId = parseInt(evt.item.dataset.id);
                    const cards = Array.from(grid.children);
                    const payload = cards.map((card, index) => ({
                        id: parseInt(card.dataset.id),
                        sort_order: index + 1
                    }));

                    fetch("{{ route('admin.pages.update-order') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(payload)
                    });
                }
            });
        }
    </script>
@endpush