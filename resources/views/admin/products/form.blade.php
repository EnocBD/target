@extends('layouts.admin')

@section('title', $product->exists ? 'Editar Producto' : 'Nuevo Producto')
@section('page-title', $product->exists ? 'Editar Producto: ' . $product->name : 'Nuevo Producto')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span>
        <a href="{{ route('admin.products.index') }}" class="text-primary hover:text-primary-700">Productos</a>
    </li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">{{ $product->exists ? 'Editar' : 'Nuevo' }}</span></li>
@endpush

@push('styles')
    <style>
        .media-item {
            transition: all 0.3s ease;
        }
        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .drop-zone {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .drop-zone.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .sortable-ghost {
            opacity: 0.4;
            background: #f0f9ff;
        }
        .sortable-drag {
            opacity: 0.9;
        }

        /* YouTube Button Styles */
        .ql-snow .ql-toolbar button.ql-youtube {
            width: 32px;
            height: 32px;
            padding: 4px;
        }

        .ql-snow .ql-toolbar button.ql-youtube:hover {
            background-color: #f0f0f0;
        }

        .ql-snow .ql-toolbar button.ql-youtube svg {
            display: block;
            margin: 0 auto;
        }

        /* Responsive iframe styles for YouTube embeds */
        .ql-editor .ratio {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
        }

        .ql-editor .ratio iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
@endpush

@section('content')
    <div x-data="productForm()">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-box mr-2 text-primary"></i>
            {{ $product->exists ? 'Editar Producto: ' . $product->name : 'Crear nuevo Producto' }}
        </h2>

        <!-- YouTube URL Modal -->
        <div id="youtube-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Insertar Video de YouTube</h3>
                <input type="url" id="youtube-url" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://www.youtube.com/watch?v=..." />
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeYoutubeModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">Cancelar</button>
                    <button type="button" onclick="insertYoutubeVideo()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Insertar</button>
                </div>
            </div>
        </div>

        <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" class="space-y-8">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <!-- Información Básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label">Nombre del Producto *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                           class="form-input {{ $errors->has('name') ? 'border-red-500' : '' }}"
                           required placeholder="Ingrese el nombre del producto">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="order" class="form-label">Orden</label>
                    <input type="number" id="order" name="order" value="{{ old('order', $product->order ?? 0) }}"
                           class="form-input" placeholder="0" min="0">
                    @error('order')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="form-label">Descripción</label>
                <div class="quill-wrapper">
                    <div id="description-editor" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 200px;"></div>
                    <textarea id="description" name="description" rows="6" class="hidden">{{ old('description', $product->description) }}</textarea>
                </div>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="excerpt" class="form-label">Extracto/Resumen</label>
                <textarea id="excerpt" name="excerpt" rows="3" class="form-input"
                          placeholder="Breve resumen del producto (opcional)">{{ old('excerpt', $product->excerpt) }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Un breve extracto que se mostrará en listados y vistas previas.</p>
                @error('excerpt')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- SEO Fields -->
            <div class="pt-6 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-900 mb-4">SEO y Metadatos</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="form-label">Meta Título</label>
                        <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                               class="form-input" placeholder="Título para SEO (60 caracteres recomendado)">
                        @error('meta_title')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="form-label">Meta Descripción</label>
                        <input type="text" id="meta_description" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}"
                               class="form-input" placeholder="Descripción para SEO (160 caracteres recomendado)">
                        @error('meta_description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
                <label class="flex items-center">
                    <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }}
                           class="form-checkbox mr-2">
                    <span class="text-sm font-medium text-gray-700">Producto Activo</span>
                </label>
            </div>

            <!-- Media Management -->
            @include('admin.components.media-manager', ['mediaItems' => $product->media ?? [], 'entityId' => $product->id ?? null, 'entityType' => 'product'])

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <button type="submit" class="btn btn-primary" :disabled="isUploading">
                    <span x-show="!isUploading">
                        <i class="fas fa-save mr-2"></i>
                        {{ $product->exists ? 'Actualizar Producto' : 'Crear Producto' }}
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
@endsection

@push('scripts')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        function productForm() {
            return {
                media: @json($product->media ?? []),
                activeTab: 'all',
                isUploading: false,
                previewMedia: null,
                productId: {{ $product->id ?? 'null' }},
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
                    if (!this.productId) {
                        alert('Por favor, guarde el producto primero antes de subir archivos.');
                        return;
                    }

                    this.isUploading = true;

                    for (const file of files) {
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('type', this.getMediaType(file));

                        try {
                            const url = `{{ route('admin.products.media.upload', $product->id ?? ':productId') }}`
                                .replace(':productId', this.productId);

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
                    event.target.value = '';
                },

                getMediaType(file) {
                    if (file.type.startsWith('image/')) return 'image';
                    if (file.type.startsWith('video/')) return 'video';
                    return 'document';
                },

                async deleteMedia(mediaId) {
                    if (!confirm('¿Está seguro de eliminar este archivo?')) return;

                    try {
                        const url = `{{ route('admin.products.media.delete', [$product->id ?? ':productId', ':mediaId']) }}`
                            .replace(':productId', this.productId)
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
                    // Prevent duplicate calls
                    if (this.isReordering) {
                        console.log('Reorder already in progress, skipping...');
                        return;
                    }

                    this.isReordering = true;

                    try {
                        console.log('Reordering media with IDs:', mediaIds);

                        // Build URL with explicit base
                        const baseUrl = window.location.origin;
                        const url = `{{ route('admin.products.media.reorder', $product->id ?? ':productId') }}`
                            .replace(':productId', this.productId);

                        console.log('Sending reorder request to:', url);

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ media_ids: mediaIds })
                        });

                        console.log('Response status:', response.status);

                        if (response.ok) {
                            const result = await response.json();
                            console.log('API response:', result);

                            if (result.success) {
                                console.log('✅ Media reordered successfully in database');
                            } else {
                                console.error('❌ Server error:', result.message);
                            }
                        } else {
                            console.error('❌ HTTP error:', response.status);
                            const errorText = await response.text();
                            console.error('Error response:', errorText);
                        }

                        console.log('✅ Media reordered successfully in database - DOM order preserved');

                    } catch (error) {
                        console.error('❌ Network error:', error);
                    } finally {
                        this.isReordering = false;
                    }
                },

                previewMedia(item) {
                    // Find the modal element and set its previewMedia
                    const modal = document.querySelector('[x-data*="previewMedia"]');
                    if (modal && modal._x_dataStack) {
                        modal._x_dataStack[0].previewMedia = item;
                    } else if (modal) {
                        // Initialize Alpine data if not exists
                        Alpine.initTree(modal);
                        modal._x_dataStack[0].previewMedia = item;
                    }
                },

                formatFileSize(bytes) {
                    if (!bytes) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        }
    </script>

    <script>
        // Initialize Quill Editor when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill Editor
            const quillOptions = {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['clean'],
                        ['link', 'image']
                    ]
                },
                placeholder: 'Escribe la descripción del producto...'
            };

            const quill = new Quill('#description-editor', quillOptions);
            const textarea = document.getElementById('description');

            // Add custom YouTube button to toolbar after initialization
            const toolbar = quill.getModule('toolbar');
            const youtubeButton = document.createElement('button');
            youtubeButton.type = 'button';
            youtubeButton.className = 'ql-youtube';
            youtubeButton.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" style="fill: #FF0000;"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
            youtubeButton.title = 'Insertar Video de YouTube';

            // Add click handler
            youtubeButton.addEventListener('click', function() {
                showYoutubeModal();
            });

            // Find the last button group and add YouTube button
            const toolbarContainer = toolbar.container;
            const lastButtonGroup = toolbarContainer.querySelector('.ql-formats:last-child');
            if (lastButtonGroup) {
                lastButtonGroup.appendChild(youtubeButton);
            } else {
                // Fallback: add to the container directly
                toolbarContainer.appendChild(youtubeButton);
            }

            // Set initial content
            if (textarea.value) {
                quill.root.innerHTML = textarea.value;
            }

            // Sync content
            quill.on('text-change', function() {
                textarea.value = quill.root.innerHTML;
            });

            // Handle image uploads
            quill.getModule('toolbar').addHandler('image', function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;

                    const range = quill.getSelection(true);
                    quill.insertText(range.index, '[Subiendo imagen...]', 'user');
                    quill.setSelection(range.index + '[Subiendo imagen...]'.length);

                    try {
                        const formData = new FormData();
                        formData.append('image', file);

                        const response = await fetch('{{ route("admin.blocks.quill-image") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        quill.deleteText(range.index, '[Subiendo imagen...]'.length);

                        if (result.success) {
                            quill.insertEmbed(range.index, 'image', result.url, 'user');
                            quill.setSelection(range.index + 1);
                        } else {
                            quill.insertText(range.index, '[Error al subir imagen]', 'user');
                        }
                    } catch (error) {
                        quill.deleteText(range.index, '[Subiendo imagen...]'.length);
                        quill.insertText(range.index, '[Error al subir imagen]', 'user');
                    }
                };
            });

            // YouTube Modal Functions
            window.showYoutubeModal = function() {
                document.getElementById('youtube-modal').classList.remove('hidden');
                document.getElementById('youtube-url').value = '';
                document.getElementById('youtube-url').focus();
            };

            window.closeYoutubeModal = function() {
                document.getElementById('youtube-modal').classList.add('hidden');
            };

            window.insertYoutubeVideo = function() {
                const url = document.getElementById('youtube-url').value.trim();

                if (!url) {
                    alert('Por favor, ingresa una URL de YouTube válida');
                    return;
                }

                const videoId = extractYoutubeVideoId(url);

                if (!videoId) {
                    alert('URL de YouTube no válida. Debe ser un link de youtube.com/watch?v=... o youtu.be/...');
                    return;
                }

                const range = quill.getSelection(true);
                const embedHtml = `<div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/${videoId}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            class="embed-responsive-item">
                    </iframe>
                </div>`;

                quill.clipboard.dangerouslyPasteHTML(range.index, embedHtml);
                closeYoutubeModal();
                quill.setSelection(range.index + 1);
            };

            window.extractYoutubeVideoId = function(url) {
                // Patrones para diferentes formatos de URL de YouTube
                const patterns = [
                    /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
                    /youtube\.com\/.*[?&]v=([^&\n?#]+)/
                ];

                for (const pattern of patterns) {
                    const match = url.match(pattern);
                    if (match && match[1]) {
                        return match[1];
                    }
                }

                return null;
            };

            // Close modal when clicking outside
            document.getElementById('youtube-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeYoutubeModal();
                }
            });

            // Handle Enter key in YouTube URL input
            document.getElementById('youtube-url').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    insertYoutubeVideo();
                }
            });
        });

        // Initialize SortableJS for media grid
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Alpine to be ready
            Alpine.nextTick(() => {
                const mediaGrid = document.querySelector('.grid[x-sortable]');
                if (mediaGrid && typeof Sortable !== 'undefined') {
                    const sortable = Sortable.create(mediaGrid, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        dragClass: 'sortable-drag',
                        handle: '.handle',
                        onEnd: function(evt) {
                            console.log('🎯 Sortable onEnd triggered');

                            // Get the Alpine component
                            const alpineComponent = Alpine.$data(mediaGrid.closest('[x-data]'));

                            if (alpineComponent) {
                                // Extract IDs directly from DOM elements using their data attributes
                                const domElements = Array.from(mediaGrid.children).filter(el => el.tagName === 'DIV');
                                const reorderedIds = [];

                                domElements.forEach((domEl, index) => {
                                    // Extract media ID from data attribute
                                    let mediaId = domEl.getAttribute('data-media-id');

                                    if (mediaId) {
                                        mediaId = parseInt(mediaId);
                                        reorderedIds.push(mediaId);
                                        console.log(`📍 DOM position ${index}: Media ID ${mediaId}`);
                                    } else {
                                        console.warn(`⚠️ Could not find media ID for element at position ${index}`, domEl);
                                    }
                                });

                                console.log('🔄 Final media IDs:', reorderedIds);

                                if (reorderedIds.length > 0) {
                                    // Call the NEW simplified reorderMedia function
                                    alpineComponent.reorderMedia(reorderedIds);
                                } else {
                                    console.error('❌ Could not extract any media IDs');
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush