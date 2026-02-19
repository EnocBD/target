@extends('layouts.admin')

@section('title', 'Bloques')
@section('page-title', 'Bloques')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Bloques</span></li>
@endpush

@section('content')
    <div x-data="{ showDeleteModal: false, blockId: null, selectedBlocks: [] }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bloques de Contenido</h1>
                <p class="text-gray-600 mt-1">
                    @if(request('page_id'))
                        @php $selectedPage = $pages->find(request('page_id')); @endphp
                        Bloques de "{{ $selectedPage->title ?? 'Página' }}"
                    @else
                        Administra los bloques de contenido del sitio
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.blocks.create', ['page_id' => request('page_id')]) }}"
                   class="btn btn-primary group shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                    Nuevo Bloque
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
            <form method="GET" action="{{ route('admin.blocks.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Filtrar por página</label>
                    <select name="page_id" class="form-select" required>
                        <option value="">Selecciona una página</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ request('page_id') == $page->id ? 'selected' : '' }}>
                                {{ $page->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn btn-primary flex-1">
                        <i class="fas fa-filter mr-2"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('admin.blocks.index') }}" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($blocks->count() > 0 && request('page_id'))
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th>Bloque</th>
                            <th>Página</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Orden</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="blocks-sortable">
                        @foreach($blocks as $block)
                            <tr data-id="{{ $block->id }}">
                                <td>
                                    <div class="flex items-center">
                                        @php
                                            $layoutConfig = config("blocks.layouts.{$block->block_type}", []);
                                            $icon = $layoutConfig['icon'] ?? 'fas fa-cube';
                                            $title = $block->getDataValue('title') ?: ($layoutConfig['name'] ?? 'Bloque sin título');
                                        @endphp
                                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-primary to-primary-700 rounded-lg flex items-center justify-center">
                                            <i class="{{ $icon }} text-white"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-semibold text-gray-900">{{ $title }}</div>
                                            @if($block->parent)
                                                <div class="text-sm text-gray-500">Sub-bloque de: {{ $block->parent->getDataValue('title', 'Bloque padre') }}</div>
                                            @elseif($block->children->count() > 0)
                                                <div class="text-sm text-blue-600">{{ $block->children->count() }} sub-bloques</div>
                                            @endif
                                        </div>
                                        <div class="ml-2">
                                            <i class="fas fa-grip-vertical text-gray-400 cursor-move"></i>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($block->page)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $block->page->title }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Sin página</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-800 font-mono text-sm">
                                        {{ $layoutConfig['name'] ?? $block->block_type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-700 rounded-full font-medium">
                                        {{ $block->sort_order }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($block->is_active ?? true)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check text-xs mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-xmark text-xs mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-gray-900">{{ $block->updated_at->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $block->updated_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.blocks.edit', $block) }}"
                                           class="action-btn action-btn-edit"
                                           title="Editar bloque">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <button type="button"
                                                class="action-btn action-btn-delete"
                                                title="Eliminar bloque"
                                                @click="blockId = {{ $block->id }}; showDeleteModal = true">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($blocks->hasPages())
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Mostrando {{ $blocks->firstItem() }}-{{ $blocks->lastItem() }} de {{ $blocks->total() }} bloques
                        </div>
                        <div class="pagination-nav">
                            {{ $blocks->links() }}
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-cubes empty-state-icon"></i>
                    @if(!request('page_id'))
                        <h3 class="empty-state-title">Selecciona una página</h3>
                        <p class="empty-state-description">
                            Para ver y gestionar los bloques, primero selecciona una página en el filtro de arriba.
                        </p>
                    @else
                        <h3 class="empty-state-title">No hay bloques creados</h3>
                        <p class="empty-state-description">
                            No se encontraron bloques para esta página. Comienza añadiendo el primer bloque.
                        </p>
                        <a href="{{ route('admin.blocks.create', ['page_id' => request('page_id')]) }}"
                           class="btn btn-primary group shadow-lg">
                            <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                            Crear Primer Bloque
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Delete Modal -->
        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="showDeleteModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <i class="fas fa-exclamation-triangle h-6 w-6 text-red-600"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900">
                                    Confirmar eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        ¿Estás seguro de que deseas eliminar este bloque? Esta acción no se puede deshacer y se perderán todos los datos asociados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                        <button @click="showDeleteModal = false"
                                class="btn btn-secondary w-full sm:w-auto">
                            Cancelar
                        </button>
                        <form :action="'{{ route('admin.blocks.destroy', ':id') }}'.replace(':id', blockId)"
                              method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-danger w-full sm:w-auto">
                                <i class="fas fa-trash mr-2"></i>
                                Eliminar bloque
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            window.duplicateBlock = function(blockId) {
                if (confirm('¿Deseas duplicar este bloque?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/blocks/${blockId}/duplicate`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfToken);

                    document.body.appendChild(form);
                    form.submit();
                }
            };
        });

        // Initialize sortable functionality when page is selected
        const sortableTable = document.getElementById('blocks-sortable');
        if (sortableTable) {
            new Sortable(sortableTable, {
                animation: 150,
                handle: '.cursor-move',
                onEnd: evt => {
                    const movedId = parseInt(evt.item.dataset.id);
                    const rows = Array.from(sortableTable.children);
                    const payload = rows.map((row, index) => ({
                        id: parseInt(row.dataset.id),
                        sort_order: index + 1
                    }));

                    fetch("{{ route('admin.blocks.update-order') }}", {
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