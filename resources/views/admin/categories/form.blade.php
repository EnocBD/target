@extends('layouts.admin')

@section('title', $category->exists ? 'Editar Categoría' : 'Nueva Categoría')
@section('page-title', $category->exists ? 'Editar Categoría: ' . $category->name : 'Nueva Categoría')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span>
        <a href="{{ route('admin.categories.index') }}" class="text-primary hover:text-primary-700">Categorías</a>
    </li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">{{ $category->exists ? 'Editar' : 'Nueva' }}</span></li>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush

@section('content')
    <div x-data="categoryForm()">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-tag mr-2 text-primary"></i>
            {{ $category->exists ? 'Editar Categoría: ' . $category->name : 'Crear nueva Categoría' }}
        </h2>

        <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              method="POST"
              class="space-y-8">
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <!-- Información Básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label">Nombre de la Categoría *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                           class="form-input {{ $errors->has('name') ? 'border-red-500' : '' }}"
                           required placeholder="Ingrese el nombre de la categoría">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="form-label">Orden</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                           class="form-input" placeholder="0" min="0">
                    @error('sort_order')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="form-label">Descripción</label>
                <textarea id="description" name="description" rows="4" class="form-input"
                          placeholder="Descripción de la categoría">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                           class="form-checkbox h-5 w-5 text-primary border-gray-300 rounded focus:ring-primary">
                    <span class="ml-2 text-gray-900">Categoría Activa</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Las categorías inactivas no se mostrarán en el sitio.</p>
            </div>

            <!-- Media Management -->
            @include('admin.components.media-manager', ['mediaItems' => $category->media ?? [], 'entityId' => $category->id ?? null, 'entityType' => 'category'])

            <!-- SEO Fields -->
            <div class="pt-6 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-900 mb-4">SEO y Metadatos</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="form-label">Meta Título</label>
                        <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}"
                               class="form-input" placeholder="Título para SEO (60 caracteres recomendado)">
                        @error('meta_title')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="form-label">Meta Descripción</label>
                        <input type="text" id="meta_description" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}"
                               class="form-input" placeholder="Descripción para SEO (160 caracteres recomendado)">
                        @error('meta_description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary" :disabled="isUploading">
                    <span x-show="!isUploading">
                        <i class="fas fa-save mr-2"></i>
                        {{ $category->exists ? 'Actualizar' : 'Crear' }} Categoría
                    </span>
                    <span x-show="isUploading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Media Preview Modal -->
    <div x-data="{ previewMedia: null }" x-show="previewMedia"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div @click.away="previewMedia = null" class="relative max-w-4xl w-full max-h-full">
            <button type="button" @click="previewMedia = null"
                    class="absolute -top-2 -right-2 bg-white text-gray-700 rounded-full p-2 hover:bg-gray-100 z-10">
                <i class="fas fa-xmark"></i>
            </button>
            <template x-if="previewMedia?.type === 'image'">
                <img :src="previewMedia?.file_url" :alt="previewMedia?.original_name"
                     class="max-w-full max-h-[80vh] object-contain rounded-lg">
            </template>
            <template x-if="previewMedia?.type === 'video'">
                <video controls class="max-w-full max-h-[80vh] rounded-lg">
                    <source :src="previewMedia?.file_url" :type="previewMedia?.mime_type">
                </video>
            </template>
        </div>
    </div>

    @push('scripts')
        <script>
        function categoryForm() {
            return {
                media: @json($category->media ?? []),
                activeTab: 'all',
                isUploading: false,
                previewMedia: null,
                categoryId: {{ $category->id ?? 'null' }},
                isReordering: false,

                get filteredMedia() {
                    if (this.activeTab === 'all') return this.media;
                    return this.media.filter(item => item.type === this.activeTab);
                },

                async handleDrop(event) {
                    event.target.classList.remove('dragover');
                    const files = Array.from(event.dataTransfer.files);
                    await this.uploadFiles(files);
                },

                async handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    await this.uploadFiles(files);
                },

                async uploadFiles(files) {
                    if (!this.categoryId) {
                        alert('Por favor, guarde la categoría primero antes de subir archivos.');
                        return;
                    }

                    this.isUploading = true;

                    for (const file of files) {
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('type', this.getMediaType(file));

                        try {
                            const url = `{{ route('admin.categories.media.upload', ':categoryId') }}`
                                .replace(':categoryId', this.categoryId);

                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                body: formData
                            });

                            const result = await response.json();
                            if (result.success) {
                                this.media.push(result.media);
                            } else {
                                alert('Error al subir archivo: ' + result.message);
                            }
                        } catch (error) {
                            alert('Error al subir archivo: ' + error.message);
                        }
                    }

                    this.isUploading = false;
                    if (event && event.target) {
                        event.target.value = '';
                    }
                },

                getMediaType(file) {
                    if (file.type.startsWith('image/')) return 'image';
                    if (file.type.startsWith('video/')) return 'video';
                    return 'document';
                },

                async deleteMedia(mediaId) {
                    if (!confirm('¿Está seguro de eliminar este archivo?')) return;

                    try {
                        const url = `{{ route('admin.categories.media.delete', [':categoryId', ':mediaId']) }}`
                            .replace(':categoryId', this.categoryId)
                            .replace(':mediaId', mediaId);

                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            }
                        });

                        const result = await response.json();
                        if (result.success) {
                            this.media = this.media.filter(m => m.id !== mediaId);
                        } else {
                            alert('Error al eliminar archivo: ' + result.message);
                        }
                    } catch (error) {
                        alert('Error al eliminar archivo: ' + error.message);
                    }
                },

                async reorderMedia(mediaIds) {
                    if (this.isReordering) return;

                    this.isReordering = true;

                    try {
                        const url = `{{ route('admin.categories.media.reorder', ':categoryId') }}`
                            .replace(':categoryId', this.categoryId);

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ media_ids: mediaIds })
                        });

                        const result = await response.json();
                        if (!result.success) {
                            alert('Error al reordenar archivos');
                        }
                    } catch (error) {
                        alert('Error al reordenar archivos: ' + error.message);
                    } finally {
                        this.isReordering = false;
                    }
                },

                setPreviewMedia(mediaItem) {
                    this.previewMedia = mediaItem;
                },

                initSortable() {
                    const container = document.querySelector('.media-grid');
                    if (container) {
                        new Sortable(container, {
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            dragClass: 'sortable-drag',
                            onEnd: (evt) => {
                                const item = this.media.splice(evt.oldIndex, 1)[0];
                                this.media.splice(evt.newIndex, 0, item);
                                this.reorderMedia();
                            }
                        });
                    }
                }
            };
        }
        </script>
    @endpush
@endsection
