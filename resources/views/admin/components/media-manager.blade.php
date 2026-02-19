{{-- Media Manager Component --}}
{{-- Usage: @include('admin.components.media-manager', ['mediaItems' => $media, 'entityId' => $product->id, 'entityType' => 'product']) --}}

<div x-data="mediaManager({{ $entityId }}, '{{ $entityType }}')">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Archivos Multimedia</h3>

    <!-- Upload Area -->
    <div class="mb-6">
        <label class="form-label">Subir Archivos</label>
        <div class="drop-zone p-8 text-center rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
             @dragover.prevent="$el.classList.add('dragover')"
             @dragleave.prevent="$el.classList.remove('dragover')"
             @drop.prevent="handleDrop($event)"
             @click="$refs.fileInput.click()">
            <i class="fas fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-600 mb-2">
                <span class="font-medium">Arrastra archivos aquí o haz clic para seleccionar</span>
            </p>
            <p class="text-sm text-gray-500">Imágenes, videos y documentos (Máx. 10MB)</p>
            <input type="file" x-ref="fileInput" class="hidden" multiple accept="image/*,video/*,.pdf,.doc,.docx" @change="handleFileSelect($event)">
        </div>
    </div>

    <!-- Media Type Tabs -->
    <div class="mb-4">
        <div class="flex space-x-1 border-b border-gray-200">
            <button type="button" @click="activeTab = 'all'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-600': activeTab === 'all',
                        'px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors': true
                    }">
                Todos (<span x-text="media.length"></span>)
            </button>
            <button type="button" @click="activeTab = 'image'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-600': activeTab === 'image',
                        'px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors': true
                    }">
                Imágenes (<span x-text="media.filter(item => item.type === 'image').length"></span>)
            </button>
            <button type="button" @click="activeTab = 'video'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-600': activeTab === 'video',
                        'px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors': true
                    }">
                Videos (<span x-text="media.filter(item => item.type === 'video').length"></span>)
            </button>
            <button type="button" @click="activeTab = 'document'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-600': activeTab === 'document',
                        'px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors': true
                    }">
                Documentos (<span x-text="media.filter(item => item.type === 'document').length"></span>)
            </button>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" x-sortable :handle="'.handle'">
        <template x-for="item in filteredMedia" :key="item.id">
            <div class="media-item bg-white border border-gray-200 rounded-lg overflow-hidden" :data-media-id="item.id">
                <!-- Media Content -->
                <div class="relative">
                    <template x-if="item.type === 'image'">
                        <img :src="item.thumbnail_url || item.file_url" :alt="item.original_name"
                             class="w-full h-48 object-cover">
                    </template>
                    <template x-if="item.type === 'video'">
                        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-video text-4xl text-gray-400"></i>
                        </div>
                    </template>
                    <template x-if="item.type === 'document'">
                        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-file-pdf text-4xl text-red-500"></i>
                        </div>
                    </template>

                    <!-- Controls -->
                    <div class="absolute top-2 right-2 flex space-x-1">
                        <button type="button" @click="previewMedia(item)"
                                class="p-2 bg-white/90 hover:bg-white text-gray-700 rounded-md shadow-sm transition-colors"
                                title="Vista previa">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                        <button type="button" @click="deleteMedia(item.id)"
                                class="p-2 bg-white/90 hover:bg-white text-red-600 rounded-md shadow-sm transition-colors"
                                title="Eliminar">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>

                    <!-- Drag Handle -->
                    <div class="handle absolute top-2 left-2 p-2 bg-white/90 rounded-md shadow-sm cursor-move">
                        <i class="fas fa-grip-vertical text-gray-500 text-sm"></i>
                    </div>
                </div>

                <!-- Media Info -->
                <div class="p-3">
                    <p class="text-sm font-medium text-gray-900 truncate" x-text="item.original_name"></p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-500" x-text="formatFileSize(item.size)"></span>
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="{
                                  'bg-green-100 text-green-800': item.type === 'image',
                                  'bg-purple-100 text-purple-800': item.type === 'video',
                                  'bg-blue-100 text-blue-800': item.type === 'document'
                              }" x-text="item.type"></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="filteredMedia.length === 0" class="col-span-full text-center py-12">
            <i class="fas fa-image text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No hay archivos de este tipo</p>
        </div>
    </div>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function mediaManager(entityId, entityType) {
    return {
        media: @json($mediaItems ?? []),
        activeTab: 'all',
        isUploading: false,
        isReordering: false,
        entityId: entityId,
        entityType: entityType,

        get filteredMedia() {
            if (this.activeTab === 'all') return this.media;
            return this.media.filter(item => item.type === this.activeTab);
        },

        // Función para obtener el nombre de la ruta correcto (maneja excepciones de plurales)
        getRouteName() {
            // Mapeo de excepciones de plurales
            const pluralExceptions = {
                'category': 'categories',
                'product': 'products',
                'brand': 'brands',
                'page': 'pages',
                'user': 'users',
                'block': 'blocks'
            };

            return pluralExceptions[this.entityType] || `${this.entityType}s`;
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
            if (!this.entityId) {
                alert('Por favor, guarde el elemento primero antes de subir archivos.');
                return;
            }

            this.isUploading = true;

            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', this.getMediaType(file));

                try {
                    const routeName = this.getRouteName();
                    const url = `{{ url('/') }}/admin/${routeName}/${this.entityId}/media`;

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
                const routeName = this.getRouteName();
                const url = `{{ url('/') }}/admin/${routeName}/${this.entityId}/media/${mediaId}`;

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
            try {
                const routeName = this.getRouteName();
                const url = `{{ url('/') }}/admin/${routeName}/${this.entityId}/media/reorder`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        media_ids: mediaIds
                    })
                });

                const result = await response.json();
                if (!result.success) {
                    alert('Error al reordenar archivos: ' + result.message);
                }
            } catch (error) {
                alert('Error al reordenar archivos: ' + error.message);
            }
        },

        previewMedia(item) {
            const modal = document.querySelector('[x-data*="previewMedia"]');
            if (modal && modal._x_dataStack) {
                modal._x_dataStack[0].previewMedia = item;
            } else if (modal) {
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
    };
}

// Initialize SortableJS for all media managers
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Alpine to be ready
    Alpine.nextTick(() => {
        document.querySelectorAll('.grid[x-sortable]').forEach(mediaGrid => {
            if (typeof Sortable !== 'undefined') {
                const sortable = Sortable.create(mediaGrid, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    handle: '.handle',
                    onEnd: function(evt) {
                        console.log('🎯 Sortable onEnd triggered');

                        const alpineComponent = Alpine.$data(mediaGrid.closest('[x-data]'));

                        if (alpineComponent) {
                            const domElements = Array.from(mediaGrid.children).filter(el => el.tagName === 'DIV');
                            const reorderedIds = [];

                            domElements.forEach((domEl, index) => {
                                let mediaId = domEl.getAttribute('data-media-id');
                                if (mediaId) {
                                    mediaId = parseInt(mediaId);
                                    reorderedIds.push(mediaId);
                                    console.log(`📍 DOM position ${index}: Media ID ${mediaId}`);
                                }
                            });

                            console.log('🔄 Final media IDs:', reorderedIds);

                            if (reorderedIds.length > 0) {
                                alpineComponent.reorderMedia(reorderedIds);
                            }
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush