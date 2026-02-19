@extends('layouts.visual-editor')

@section('title', isset($block->id) ? 'Editar Bloque' : 'Nuevo Bloque')
@section('editor-title', isset($block->id) ? 'Editando: ' . ($layoutsConfig[$block->block_type]['name'] ?? $block->block_type) : 'Nuevo Bloque')

@if(isset($block->id) && $block->block_type)
    @section('preview-url', route('admin.blocks.iframe-preview', ['blockId' => $block->id]))
@else
    @section('preview-url', route('admin.blocks.iframe-preview'))
@endif

@section('form-content')
<!-- EL FORMULARIO ORIGINAL COMPLETO AQUÍ -->
<div x-data="{ showPreview: false }">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">{{ isset($block->id) ? 'Editar Bloque' : 'Crear Bloque' }}</h2>
        <p class="text-gray-600 mt-1">{{ isset($block->id) ? 'Modifica la configuración y contenido del bloque' : 'Configure el nuevo bloque de contenido' }}</p>
    </div>

    {{ html()->modelForm(isset($block->id) ? $block : new \App\Models\Block(), 'POST', isset($block->id) ? route('admin.blocks.update', $block->id) : route('admin.blocks.store'))->id('blockForm')->attribute('enctype', 'multipart/form-data')->open() }}

    @if(isset($block->id))
        {{ html()->hidden('_method', 'PUT') }}
    @endif

    <div class="space-y-6">
        <!-- Información General -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-primary"></i>
                    Información General
                </h3>
            </div>
            <div class="p-4">
                <div class="space-y-4">
                    <div>
                        <label for="page_id" class="form-label">Página</label>
                        {{ html()->select('page_id',
                            ['' => 'Sin página específica'] + $pages->pluck('title', 'id')->toArray(),
                            old('page_id', $block->page_id ?? request('page_id'))
                        )->class('form-select' . ($errors->has('page_id') ? ' border-red-500' : '')) }}
                        @error('page_id')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="block_type" class="form-label">Tipo de bloque *</label>
                        @if(isset($block->id))
                            <!-- Block type is locked when editing -->
                            {{ html()->hidden('block_type', $block->block_type) }}
                            <div class="flex items-center p-3 bg-gray-100 border border-gray-300 rounded-md">
                                <i class="fas fa-lock text-gray-500 mr-3"></i>
                                <div>
                                    <span class="font-medium text-gray-900">{{ $layoutsConfig[$block->block_type]['name'] ?? $block->block_type }}</span>
                                </div>
                            </div>
                        @else
                            {{ html()->select('block_type',
                                ['' => 'Selecciona un tipo'] + collect($layoutsConfig)->sortBy('name')->mapWithKeys(fn($config, $key) => [$key => $config['name']])->toArray(),
                                old('block_type', $block->block_type ?? '')
                            )->class('form-select' . ($errors->has('block_type') ? ' border-red-500' : ''))->id('layout-select')->required() }}
                        @endif
                        @error('block_type')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        {{ html()->hidden('is_active', 0) }}
                        <label for="is_active" class="flex items-center space-x-3 cursor-pointer">
                            {{ html()->checkbox('is_active', old('is_active', $block->is_active ?? true), 1)
                                ->class('form-checkbox text-primary focus:ring-primary-500 focus:ring-2')
                                ->id('is_active') }}
                            <div>
                                <span class="text-gray-700 font-medium">Bloque activo</span>
                            </div>
                        </label>
                    </div>
                    @error('is_active')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Layout Description -->
                <div id="layout-description" class="hidden mt-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <h4 id="layout-name" class="font-semibold text-blue-900"></h4>
                                <p id="layout-desc" class="text-blue-800 mt-1"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Fields -->
        <div id="dynamic-fields-container"></div>

        <!-- Styles Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-palette mr-2 text-primary"></i>
                    Estilos Personalizados
                </h3>
            </div>
            <div class="p-4">
                <div x-data="{ activeTab: 'spacing' }" class="styles-editor">
                    <!-- Tabs -->
                    <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                        <button type="button"
                                @click="activeTab = 'spacing'"
                                :class="activeTab === 'spacing' ? 'bg-white shadow text-primary' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 text-sm font-medium rounded-md transition-colors">
                            Espaciado
                        </button>
                        <button type="button"
                                @click="activeTab = 'background'"
                                :class="activeTab === 'background' ? 'bg-white shadow text-primary' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 text-sm font-medium rounded-md transition-colors">
                            Fondo
                        </button>
                        <button type="button"
                                @click="activeTab = 'colors'"
                                :class="activeTab === 'colors' ? 'bg-white shadow text-primary' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 text-sm font-medium rounded-md transition-colors">
                            Colores
                        </button>
                        <button type="button"
                                @click="activeTab = 'borders'"
                                :class="activeTab === 'borders' ? 'bg-white shadow text-primary' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 text-sm font-medium rounded-md transition-colors">
                            Bordes
                        </button>
                        <button type="button"
                                @click="activeTab = 'custom'"
                                :class="activeTab === 'custom' ? 'bg-white shadow text-primary' : 'text-gray-600 hover:text-gray-900'"
                                class="px-3 py-2 text-sm font-medium rounded-md transition-colors">
                            CSS Custom
                        </button>
                    </div>

                    <!-- Spacing Tab -->
                    <div x-show="activeTab === 'spacing'" class="space-y-6">
                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Padding (Espaciado Interno)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="form-label text-sm">Superior</label>
                                    <input type="text" name="styles[padding_top]"
                                           value="{{ old('styles.padding_top', $block->getStyleValue('padding_top') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Derecha</label>
                                    <input type="text" name="styles[padding_right]"
                                           value="{{ old('styles.padding_right', $block->getStyleValue('padding_right') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Inferior</label>
                                    <input type="text" name="styles[padding_bottom]"
                                           value="{{ old('styles.padding_bottom', $block->getStyleValue('padding_bottom') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Izquierda</label>
                                    <input type="text" name="styles[padding_left]"
                                           value="{{ old('styles.padding_left', $block->getStyleValue('padding_left') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Margin (Espaciado Externo)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="form-label text-sm">Superior</label>
                                    <input type="text" name="styles[margin_top]"
                                           value="{{ old('styles.margin_top', $block->getStyleValue('margin_top') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Derecha</label>
                                    <input type="text" name="styles[margin_right]"
                                           value="{{ old('styles.margin_right', $block->getStyleValue('margin_right') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Inferior</label>
                                    <input type="text" name="styles[margin_bottom]"
                                           value="{{ old('styles.margin_bottom', $block->getStyleValue('margin_bottom') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                                <div>
                                    <label class="form-label text-sm">Izquierda</label>
                                    <input type="text" name="styles[margin_left]"
                                           value="{{ old('styles.margin_left', $block->getStyleValue('margin_left') ?? '') }}"
                                           class="form-input" placeholder="0px">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Tab -->
                    <div x-show="activeTab === 'background'" class="space-y-6">
                        <div>
                            <label class="form-label">Color de Fondo</label>
                            <input type="color" name="styles[background_color]"
                                   value="{{ old('styles.background_color', $block->getStyleValue('background_color') ?? '#ffffff') }}"
                                   class="form-input w-20 h-10">
                        </div>

                        <div>
                            <label class="form-label">Imagen de Fondo</label>
                            <input type="url" name="styles[background_image]"
                                   value="{{ old('styles.background_image', $block->getStyleValue('background_image') ?? '') }}"
                                   class="form-input" placeholder="https://...">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Tamaño</label>
                                <select name="styles[background_size]" class="form-select">
                                    <option value="">Por defecto</option>
                                    <option value="cover" {{ old('styles.background_size', $block->getStyleValue('background_size')) === 'cover' ? 'selected' : '' }}>Cubrir</option>
                                    <option value="contain" {{ old('styles.background_size', $block->getStyleValue('background_size')) === 'contain' ? 'selected' : '' }}>Contener</option>
                                    <option value="auto" {{ old('styles.background_size', $block->getStyleValue('background_size')) === 'auto' ? 'selected' : '' }}>Automático</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Posición</label>
                                <select name="styles[background_position]" class="form-select">
                                    <option value="">Por defecto</option>
                                    <option value="center" {{ old('styles.background_position', $block->getStyleValue('background_position')) === 'center' ? 'selected' : '' }}>Centro</option>
                                    <option value="top" {{ old('styles.background_position', $block->getStyleValue('background_position')) === 'top' ? 'selected' : '' }}>Superior</option>
                                    <option value="bottom" {{ old('styles.background_position', $block->getStyleValue('background_position')) === 'bottom' ? 'selected' : '' }}>Inferior</option>
                                    <option value="left" {{ old('styles.background_position', $block->getStyleValue('background_position')) === 'left' ? 'selected' : '' }}>Izquierda</option>
                                    <option value="right" {{ old('styles.background_position', $block->getStyleValue('background_position')) === 'right' ? 'selected' : '' }}>Derecha</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Repetición</label>
                                <select name="styles[background_repeat]" class="form-select">
                                    <option value="">Por defecto</option>
                                    <option value="no-repeat" {{ old('styles.background_repeat', $block->getStyleValue('background_repeat')) === 'no-repeat' ? 'selected' : '' }}>No repetir</option>
                                    <option value="repeat" {{ old('styles.background_repeat', $block->getStyleValue('background_repeat')) === 'repeat' ? 'selected' : '' }}>Repetir</option>
                                    <option value="repeat-x" {{ old('styles.background_repeat', $block->getStyleValue('background_repeat')) === 'repeat-x' ? 'selected' : '' }}>Repetir X</option>
                                    <option value="repeat-y" {{ old('styles.background_repeat', $block->getStyleValue('background_repeat')) === 'repeat-y' ? 'selected' : '' }}>Repetir Y</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Colors Tab -->
                    <div x-show="activeTab === 'colors'" class="space-y-6">
                        <div>
                            <label class="form-label">Color de Texto</label>
                            <input type="color" name="styles[color]"
                                   value="{{ old('styles.color', $block->getStyleValue('color') ?? '#000000') }}"
                                   class="form-input w-20 h-10">
                        </div>
                    </div>

                    <!-- Borders Tab -->
                    <div x-show="activeTab === 'borders'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Ancho del Borde</label>
                                <input type="text" name="styles[border_width]"
                                       value="{{ old('styles.border_width', $block->getStyleValue('border_width') ?? '') }}"
                                       class="form-input" placeholder="1px">
                            </div>
                            <div>
                                <label class="form-label">Estilo del Borde</label>
                                <select name="styles[border_style]" class="form-select">
                                    <option value="">Ninguno</option>
                                    <option value="solid" {{ old('styles.border_style', $block->getStyleValue('border_style')) === 'solid' ? 'selected' : '' }}>Sólido</option>
                                    <option value="dashed" {{ old('styles.border_style', $block->getStyleValue('border_style')) === 'dashed' ? 'selected' : '' }}>Discontinuo</option>
                                    <option value="dotted" {{ old('styles.border_style', $block->getStyleValue('border_style')) === 'dotted' ? 'selected' : '' }}>Punteado</option>
                                    <option value="double" {{ old('styles.border_style', $block->getStyleValue('border_style')) === 'double' ? 'selected' : '' }}>Doble</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Color del Borde</label>
                                <input type="color" name="styles[border_color]"
                                       value="{{ old('styles.border_color', $block->getStyleValue('border_color') ?? '#000000') }}"
                                       class="form-input w-20 h-10">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Radio del Borde</label>
                            <input type="text" name="styles[border_radius]"
                                   value="{{ old('styles.border_radius', $block->getStyleValue('border_radius') ?? '') }}"
                                   class="form-input" placeholder="0px">
                        </div>
                    </div>

                    <!-- Custom CSS Tab -->
                    <div x-show="activeTab === 'custom'" class="space-y-6">
                        <div>
                            <label class="form-label">CSS Personalizado</label>
                            <textarea name="styles[custom_css]"
                                      class="form-textarea"
                                      rows="6"
                                      placeholder="Ejemplo: font-size: 16px; line-height: 1.5;">{{ old('styles.custom_css', $block->getStyleValue('custom_css') ?? '') }}</textarea>
                            <p class="text-sm text-gray-600 mt-2">
                                Agrega propiedades CSS personalizadas. No incluyas selectores, solo las propiedades.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Block Image -->
        <div id="image-section" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-image mr-2 text-primary"></i>
                    Imagen Principal del Bloque
                </h3>
            </div>
            <div class="p-4">
                @if(isset($block->id) && $block->image_path)
                    <div class="mb-4">
                        <label class="form-label">Imagen actual</label>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            <img src="{{ asset($block->image_path) }}" alt="Imagen actual" class="w-full h-auto rounded-md shadow-sm" style="max-height: 200px; object-fit: cover;">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="btn btn-secondary btn-sm mt-2 w-full delete-current-block-image">
                                <i class="fas fa-trash"></i>
                                Eliminar imagen actual
                            </button>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="image" class="form-label">{{ isset($block->id) && $block->image_path ? 'Nueva imagen' : 'Imagen' }}</label>
                    <div class="dropzone-container">
                        <div id="image-dropzone" class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors duration-200">
                            <div class="dropzone-content">
                                <i class="fas fa-cloud-arrow-up text-3xl text-gray-400 mx-auto mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Arrastra una imagen aquí</h4>
                                <p class="text-gray-500 mb-4">o</p>
                                <button type="button" class="btn btn-primary" id="browse-files">Seleccionar archivo</button>
                                <p class="text-sm text-gray-500 mt-4">Formatos: JPG, PNG, WebP • Máximo 2MB</p>
                            </div>
                            <div class="dropzone-preview hidden">
                                <img id="preview-image" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg shadow-md mb-4">
                                <div class="flex justify-center gap-2">
                                    <button type="button" class="btn btn-secondary" id="remove-image">
                                        <i class="fas fa-trash"></i>
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </div>
                        {{ html()->file('image')->class('hidden')->id('image-input')->attribute('accept', 'image/*') }}
                    </div>
                    @error('image')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{ html()->closeModelForm() }}
</div>
@endsection

@push('scripts')
<!-- Include Quill for rich text editing -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script type="application/json" id="block-data">
@if(isset($block->id) && $block->data)
        {!! json_encode($block->getDataAsArray(), JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG) !!}
    @else
        {}
    @endif
</script>

<script type="application/json" id="layouts-config">
    {!! json_encode($layoutsConfig, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG) !!}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const layoutSelect = document.getElementById('layout-select');
        const layoutDescription = document.getElementById('layout-description');
        const dynamicFieldsContainer = document.getElementById('dynamic-fields-container');
        const imageSection = document.getElementById('image-section');

        let layoutsConfig = {};
        let currentBlockData = {};
        let quillEditors = {};

        try {
            const layoutsScript = document.getElementById('layouts-config');
            if (layoutsScript) {
                layoutsConfig = JSON.parse(layoutsScript.textContent);
            }

            const blockDataScript = document.getElementById('block-data');
            if (blockDataScript) {
                currentBlockData = JSON.parse(blockDataScript.textContent);
            }
        } catch (e) {
            console.error('Error parsing configuration data:', e);
        }

        window.assetBaseUrl = '{{ url('/') }}/';
        const isEditing = {{ isset($block->id) ? 'true' : 'false' }};

        // Helper function to escape HTML attributes
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Helper function to escape HTML for Quill input values (preserves HTML structure)
        function escapeForHtmlAttribute(html) {
            if (!html) return '';

            // Only escape quotes that would break the HTML attribute, preserve HTML tags
            return html
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Helper function to escape HTML for Quill editors
        function escapeQuillHtml(html) {
            if (!html) return '';

            // Escape only quotes that could break the HTML attribute
            return html
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Function to upload image for Quill editor
        async function uploadQuillImage(file, quill) {
            const formData = new FormData();
            formData.append('image', file);

            // Add CSRF token
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            try {
                // Show loading state
                const range = quill.getSelection(true);
                quill.insertText(range.index, '[Subiendo imagen...]', 'user');
                quill.setSelection(range.index + '[Subiendo imagen...]'.length);

                const response = await fetch('{{ route("admin.blocks.quill-image") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                // Remove loading text
                quill.deleteText(range.index, '[Subiendo imagen...]'.length);

                if (result.success) {
                    // Insert the image
                    quill.insertEmbed(range.index, 'image', result.url, 'user');
                    quill.setSelection(range.index + 1);
                } else {
                    alert('Error al subir la imagen: ' + (result.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error uploading image:', error);
                alert('Error al subir la imagen. Por favor, intenta nuevamente.');
            }
        }

        function initializeQuillEditor(textareaId, options = {}) {
            const textarea = document.getElementById(textareaId);
            if (!textarea) return null;

            const editorContainer = document.getElementById(textareaId + '-editor');
            if (!editorContainer) return null;

            const quillOptions = {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    ...options.modules
                },
                placeholder: options.placeholder || 'Escribe tu contenido aquí...',
                formats: ['header', 'bold', 'italic', 'underline', 'list', 'bullet', 'align', 'link', 'image']
            };

            const quill = new Quill(editorContainer, quillOptions);

            // Store Quill instance reference on the editor container for later access
            editorContainer.__quill = quill;

            // Set initial content
            const initialContent = textarea.value;
            if (initialContent) {
                quill.root.innerHTML = initialContent;
            }

            // Update textarea when content changes
            quill.on('text-change', function() {
                // Use Quill's root innerHTML which is the clean editor content
                textarea.value = quill.root.innerHTML;
            });

            // Handle image uploads
            quill.getModule('toolbar').addHandler('image', function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = function() {
                    const file = input.files[0];
                    if (file) {
                        uploadQuillImage(file, quill);
                    }
                };
            });

            return quill;
        }

        // Function to generate file upload field HTML
        function generateFileUploadField(fieldName, fieldConfig, prefix = '', value = '', fieldStyles = {}) {
            // Create a DOM-safe fieldId by replacing special characters
            const safeFieldId = (prefix ? `${prefix}_${fieldName}` : fieldName)
                .replace(/[\[\]]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');

            // For top-level fields (no prefix), use text[fieldName] format to match controller expectations
            const inputName = prefix ? `${prefix}[${fieldName}]` : `text[${fieldName}]`;

            const accept = fieldConfig.accept || '';
            const maxSize = fieldConfig.max_size || '2MB';

            let currentFileHtml = '';
            if (value) {
                const fileName = value.split('/').pop();
                const fileUrl = value.startsWith('http') ? value : window.assetBaseUrl + value.replace(/^\//, '');

                if (fieldConfig.type === 'image_upload') {
                    currentFileHtml = `
                        <div class="current-file mb-3">
                            <label class="form-label">Imagen actual</label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <img src="${fileUrl}" alt="Imagen actual" class="max-h-32 w-auto rounded-md shadow-sm">
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="deleteCurrentImage('${safeFieldId}', '${inputName}', '${fieldConfig.type}')">
                                <i class="fas fa-trash mr-1"></i>
                                Eliminar imagen
                            </button>
                        </div>
                    `;
                } else {
                    currentFileHtml = `
                        <div class="current-file mb-3">
                            <label class="form-label">Archivo actual</label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 flex items-center">
                                <i class="fas fa-file mr-2 text-gray-500"></i>
                                <a href="${fileUrl}" target="_blank" class="text-blue-600 hover:text-blue-800">${fileName}</a>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="deleteCurrentImage('${safeFieldId}', '${inputName}', '${fieldConfig.type}')">
                                <i class="fas fa-trash mr-1"></i>
                                Eliminar archivo
                            </button>
                        </div>
                    `;
                }
            }

            const fieldStylesSection = '';

            return `
                <div class="form-group border rounded-lg p-3 bg-white" data-field-type="${fieldConfig.type}">
                    <label for="upload_${safeFieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>

                    ${currentFileHtml}

                    <div class="upload-container">
                        <input type="file"
                               id="upload_${safeFieldId}"
                               name="upload_${inputName}"
                               class="form-input-file"
                               accept="${accept}"
                               onchange="handleFileUploadChange('${safeFieldId}', this)"
                               ${fieldConfig.required && !value ? 'required' : ''}>
                        <div class="upload-info text-sm text-gray-600 mt-1">
                            Formatos: ${accept || 'Todos'} • Máximo ${maxSize}
                        </div>
                    </div>

                    <input type="hidden" name="${inputName}" value="${escapeHtml(value)}" id="hidden_${safeFieldId}">
                    ${fieldStylesSection}
                </div>
            `;
        }

        // Function to add px automatically to dimension inputs
        function addPxHandler(input) {
            const isDimensionField = input.name.includes('padding') ||
                                   input.name.includes('margin') ||
                                   input.name.includes('width') ||
                                   input.name.includes('height') ||
                                   input.name.includes('border_width') ||
                                   input.name.includes('border_radius');

            if (!isDimensionField) return;

            const addPxValue = (value) => {
                if (!value) return value;
                value = value.trim();
                if (value && !value.endsWith('px') && !value.endsWith('%') && !value.endsWith('em') && !value.endsWith('rem') && !value.endsWith('auto') && !isNaN(value)) {
                    return value + 'px';
                }
                return value;
            };

            input.addEventListener('blur', function() {
                this.value = addPxValue(this.value);
            });

            input.addEventListener('change', function() {
                this.value = addPxValue(this.value);
            });

            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.value = addPxValue(this.value);
                }
            });
        }


        // Function to generate field HTML with individual styling options
        function generateFieldHtml(fieldName, fieldConfig, prefix = '', value = '', fieldStyles = {}) {
            const fieldId = prefix ? `${prefix}_${fieldName}` : fieldName;
            // For top-level fields (no prefix), use text[fieldName] format to match controller expectations
            const inputName = prefix ? `${prefix}[${fieldName}]` : `text[${fieldName}]`;
            const required = fieldConfig.required ? 'required' : '';
            const placeholder = fieldConfig.placeholder ? `placeholder="${escapeHtml(fieldConfig.placeholder)}"` : '';

            // Field styles section - REMOVED
            const fieldStylesSection = '';

            switch (fieldConfig.type) {
                case 'text':
                    return `
                        <div class="form-group border rounded-lg p-3 bg-white">
                            <label for="${fieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>
                            <input type="text"
                                   id="${fieldId}"
                                   name="${inputName}"
                                   class="form-input"
                                   value="${escapeHtml(value)}"
                                   ${placeholder}
                                   ${required}>
                            ${fieldStylesSection}
                        </div>
                    `;

                case 'textarea':
                    return `
                        <div class="form-group border rounded-lg p-3 bg-white">
                            <label for="${fieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>
                            <textarea id="${fieldId}"
                                      name="${inputName}"
                                      class="form-textarea"
                                      rows="3"
                                      ${placeholder}
                                      ${required}>${escapeHtml(value)}</textarea>
                            ${fieldStylesSection}
                        </div>
                    `;

                case 'editor':
                    return `
                        <div class="form-group border rounded-lg p-3 bg-white">
                            <label for="${fieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>
                            <div id="${fieldId}-editor" class="quill-editor"></div>
                            <textarea id="${fieldId}" name="${inputName}" class="hidden" ${required}>${escapeQuillHtml(value)}</textarea>
                            ${fieldStylesSection}
                        </div>
                    `;

                case 'select':
                    let optionsHtml = '';
                    if (fieldConfig.options) {
                        Object.entries(fieldConfig.options).forEach(([optValue, optLabel]) => {
                            const selected = value === optValue ? 'selected' : '';
                            optionsHtml += `<option value="${escapeHtml(optValue)}" ${selected}>${escapeHtml(optLabel)}</option>`;
                        });
                    }
                    return `
                        <div class="form-group border rounded-lg p-3 bg-white">
                            <label for="${fieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>
                            <select id="${fieldId}" name="${inputName}" class="form-select" ${required}>
                                ${!fieldConfig.required ? '<option value="">Selecciona una opción</option>' : ''}
                                ${optionsHtml}
                            </select>
                            ${fieldStylesSection}
                        </div>
                    `;

                case 'image_upload':
                case 'video_upload':
                case 'file_upload':
                    return generateFileUploadField(fieldName, fieldConfig, prefix, value, fieldStyles);

                case 'repeater':
                    return generateRepeaterField(fieldName, fieldConfig, prefix, value);

                case 'group':
                    return generateGroupField(fieldName, fieldConfig, prefix, value, fieldStyles);

                default:
                    return `
                        <div class="form-group border rounded-lg p-3 bg-white">
                            <label for="${fieldId}" class="form-label">${fieldConfig.label} ${fieldConfig.required ? '*' : ''}</label>
                            <input type="text"
                                   id="${fieldId}"
                                   name="${inputName}"
                                   class="form-input"
                                   value="${escapeHtml(value)}"
                                   ${placeholder}
                                   ${required}>
                            ${fieldStylesSection}
                        </div>
                    `;
            }
        }

        // Function to generate group field HTML
        function generateGroupField(fieldName, fieldConfig, prefix = '', value = {}, fieldStyles = {}) {
            const fieldId = prefix ? `${prefix}_${fieldName}` : fieldName;
            // For top-level groups (no prefix), use text[fieldName] format to match controller expectations
            const groupPrefix = prefix ? `${prefix}[${fieldName}]` : `text[${fieldName}]`;

            let fieldsHtml = '';

            if (fieldConfig.fields) {
                Object.entries(fieldConfig.fields).forEach(([subFieldName, subFieldConfig]) => {
                    const subValue = value && value[subFieldName] ? value[subFieldName] : (subFieldConfig.default || '');
                    const subFieldStyles = (currentBlockData.field_styles && currentBlockData.field_styles[`${fieldName}.${subFieldName}`]) || {};
                    fieldsHtml += generateFieldHtml(subFieldName, subFieldConfig, groupPrefix, subValue, subFieldStyles);
                });
            }

            const fieldStylesSection = '';

            return `
                <div class="form-group group-field border rounded-lg p-3 bg-white" data-field-type="group">
                    <h4 class="form-section-title">${fieldConfig.label}</h4>
                    <div class="group-fields bg-gray-50 p-4 rounded-lg border border-gray-200 mb-3">
                        ${fieldsHtml}
                    </div>
                    ${fieldStylesSection}
                </div>
            `;
        }

        // Function to generate repeater field HTML
        function generateRepeaterField(fieldName, fieldConfig, prefix = '', value = []) {
            const fieldId = prefix ? `${prefix}_${fieldName}` : fieldName;
            const minItems = fieldConfig.min_items || 0;
            const maxItems = fieldConfig.max_items || 10;

            let itemsHtml = '';
            const items = Array.isArray(value) ? value : [];

            // Generate existing items
            items.forEach((item, index) => {
                itemsHtml += generateRepeaterItemHtml(fieldName, fieldConfig, prefix, item, index);
            });

            // Add minimum required items if needed
            const currentItemCount = items.length;
            if (currentItemCount < minItems) {
                for (let i = currentItemCount; i < minItems; i++) {
                    itemsHtml += generateRepeaterItemHtml(fieldName, fieldConfig, prefix, {}, i);
                }
            }

            return `
                <div class="form-group repeater-field" data-field-type="repeater">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="form-section-title">${fieldConfig.label}</h4>
                        <button type="button"
                                class="btn btn-primary btn-sm add-repeater-item"
                                data-field-name="${fieldName}"
                                data-prefix="${prefix}"
                                data-max-items="${maxItems}">
                            <i class="fas fa-plus mr-1"></i>
                            Agregar elemento
                        </button>
                    </div>
                    <div class="repeater-container" id="${fieldId}-container">
                        <div class="repeater-items">
                            ${itemsHtml}
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to generate repeater item HTML
        function generateRepeaterItemHtml(fieldName, fieldConfig, prefix = '', value = {}, index = 0) {
            const fieldId = prefix ? `${prefix}_${fieldName}` : fieldName;
            // For top-level repeater items (no prefix), use text[fieldName][index] format to match controller expectations
            const itemPrefix = prefix ? `${prefix}[${fieldName}][${index}]` : `text[${fieldName}][${index}]`;

            let fieldsHtml = '';

            if (fieldConfig.fields) {
                Object.entries(fieldConfig.fields).forEach(([subFieldName, subFieldConfig]) => {
                    const subValue = value && value[subFieldName] ? value[subFieldName] : (subFieldConfig.default || '');
                    const subFieldStyles = (currentBlockData.field_styles && currentBlockData.field_styles[`${fieldName}.${index}.${subFieldName}`]) || {};
                    fieldsHtml += generateFieldHtml(subFieldName, subFieldConfig, itemPrefix, subValue, subFieldStyles);
                });
            }

            return `
                <div class="repeater-item bg-white border border-gray-200 rounded-lg p-4 mb-4" data-index="${index}">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-medium text-gray-700">Elemento ${index + 1}</h4>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm move-up-item" title="Mover arriba">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm move-down-item" title="Mover abajo">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm remove-repeater-item" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="repeater-fields">
                        ${fieldsHtml}
                    </div>
                </div>
            `;
        }

        // Function to load layout and generate fields
        function loadLayout(layoutKey) {
            if (!layoutKey || !layoutsConfig[layoutKey]) {
                dynamicFieldsContainer.innerHTML = '';
                imageSection.classList.add('hidden');
                layoutDescription.classList.add('hidden');
                return;
            }

            const layout = layoutsConfig[layoutKey];

            // Show layout description
            const layoutNameElement = document.getElementById('layout-name');
            const layoutDescElement = document.getElementById('layout-desc');

            if (layoutNameElement) layoutNameElement.textContent = layout.name;
            if (layoutDescElement) layoutDescElement.textContent = layout.description || '';

            layoutDescription.classList.remove('hidden');

            // Show/hide image section based on layout requirements
            if (layout.image_required !== false) {
                imageSection.classList.remove('hidden');
            } else {
                imageSection.classList.add('hidden');
            }

            // Generate dynamic fields
            let fieldsHtml = '';

            if (layout.fields) {
                Object.entries(layout.fields).forEach(([fieldName, fieldConfig]) => {
                    const value = currentBlockData[fieldName] || fieldConfig.default || '';
                    const fieldStyles = (currentBlockData.field_styles && currentBlockData.field_styles[fieldName]) || {};
                    fieldsHtml += generateFieldHtml(fieldName, fieldConfig, '', value, fieldStyles);
                });
            }

            dynamicFieldsContainer.innerHTML = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-pen-to-square mr-2 text-primary"></i>
                            Contenido del Bloque
                        </h3>
                    </div>
                    <div class="p-4">
                        ${fieldsHtml}
                    </div>
                </div>
            `;

            // Initialize Quill editors for any editor fields
            setTimeout(() => {
                const editorContainers = dynamicFieldsContainer.querySelectorAll('.quill-editor');
                editorContainers.forEach(container => {
                    const textareaId = container.id.replace('-editor', '');
                    const textarea = document.getElementById(textareaId);
                    if (textarea && !quillEditors[textareaId]) {
                        quillEditors[textareaId] = initializeQuillEditor(textareaId);
                    }
                });
            }, 100);

            // Attach event listeners for repeater, file upload, and dimension inputs
            attachRepeaterListeners();
            attachFileUploadListeners();
            attachDimensionInputListeners();
        }

        // Function to attach repeater listeners using event delegation
        function attachRepeaterListeners() {
            // Remove all previously attached listeners to avoid duplicates
            const oldContainer = document.querySelector('.repeater-listener-container');
            if (oldContainer) {
                oldContainer.replaceWith(oldContainer.cloneNode(true));
            }

            // Use event delegation on the dynamic fields container
            dynamicFieldsContainer.addEventListener('click', function(e) {
                // Add item button
                if (e.target.closest('.add-repeater-item')) {
                    const button = e.target.closest('.add-repeater-item');
                    const fieldName = button.dataset.fieldName;
                    const prefix = button.dataset.prefix;
                    const maxItems = parseInt(button.dataset.maxItems);

                    const containerId = prefix ? `${prefix}_${fieldName}-container` : `${fieldName}-container`;
                    const container = document.getElementById(containerId);

                    if (!container) return;

                    const itemsContainer = container.querySelector('.repeater-items');
                    const currentItems = itemsContainer.querySelectorAll('.repeater-item');

                    if (currentItems.length >= maxItems) {
                        alert(`Máximo ${maxItems} elementos permitidos`);
                        return;
                    }

                    const newIndex = currentItems.length;
                    const fieldConfig = getFieldConfigByPrefix(fieldName, prefix);

                    if (fieldConfig) {
                        const newItemHtml = generateRepeaterItemHtml(fieldName, fieldConfig, prefix, {}, newIndex);
                        itemsContainer.insertAdjacentHTML('beforeend', newItemHtml);

                        // Initialize Quill editors in the new item
                        setTimeout(() => {
                            const newItem = itemsContainer.querySelector(`.repeater-item[data-index="${newIndex}"]`);
                            if (newItem) {
                                const editorContainers = newItem.querySelectorAll('.quill-editor');
                                editorContainers.forEach(container => {
                                    const textareaId = container.id.replace('-editor', '');
                                    const textarea = document.getElementById(textareaId);
                                    if (textarea && !quillEditors[textareaId]) {
                                        quillEditors[textareaId] = initializeQuillEditor(textareaId);
                                    }
                                });
                            }
                        }, 100);

                        attachFileUploadListeners();
                        attachDimensionInputListeners();
                    }
                }

                // Remove item button - Handle both main and nested repeaters
                if (e.target.closest('.remove-repeater-item')) {
                    const item = e.target.closest('.repeater-item');
                    const container = item.closest('.repeater-items');

                    // Clean up any file previews in this item
                    const previewDivs = item.querySelectorAll('.file-preview');
                    previewDivs.forEach(div => div.remove());

                    item.remove();

                    // Get the parent repeater field to determine what type we're dealing with
                    const parentRepeaterField = container.closest('.repeater-field');

                    if (parentRepeaterField) {
                        // This could be a main repeater or nested repeater
                        // Always reindex using our comprehensive function
                        setTimeout(() => {
                            reindexRepeaterItems(container);

                            // Also reindex all sibling repeaters at the same level
                            const parentContainer = parentRepeaterField.closest('.repeater-items');
                            if (parentContainer) {
                                reindexRepeaterItems(parentContainer);
                            }
                        }, 0);
                    } else {
                        // Fallback - simple reindex
                        setTimeout(() => {
                            reindexRepeaterItems(container);
                        }, 0);
                    }
                }

                // Move up button
                if (e.target.closest('.move-up-item')) {
                    const item = e.target.closest('.repeater-item');
                    const previousItem = item.previousElementSibling;

                    if (previousItem) {
                        item.parentNode.insertBefore(item, previousItem);
                        reindexRepeaterItems(item.closest('.repeater-items'));
                    }
                }

                // Move down button
                if (e.target.closest('.move-down-item')) {
                    const item = e.target.closest('.repeater-item');
                    const nextItem = item.nextElementSibling;

                    if (nextItem) {
                        item.parentNode.insertBefore(nextItem, item);
                        reindexRepeaterItems(item.closest('.repeater-items'));
                    }
                }
            });
        }

        // Function to attach file upload listeners
        function attachFileUploadListeners() {
            // File input change listeners
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        console.log('File selected:', file.name);
                    }
                });
            });
        }

        // Function to attach dimension input listeners
        function attachDimensionInputListeners() {
            // Apply px handlers to all dimension inputs
            document.querySelectorAll('.dimension-input, input[name*="padding"], input[name*="margin"], input[name*="width"], input[name*="height"], input[name*="border_width"], input[name*="border_radius"]').forEach(input => {
                addPxHandler(input);
            });
        }

        // Helper function to get field config
        function getFieldConfig(fieldName) {
            const currentLayout = layoutSelect ? layoutSelect.value : (isEditing ? '{{ $block->block_type ?? "" }}' : '');
            if (!currentLayout || !layoutsConfig[currentLayout]) return null;

            return layoutsConfig[currentLayout].fields[fieldName] || null;
        }

        // Helper function to get field config by prefix (for nested repeaters)
        function getFieldConfigByPrefix(fieldName, prefix) {
            const currentLayout = layoutSelect ? layoutSelect.value : (isEditing ? '{{ $block->block_type ?? "" }}' : '');
            if (!currentLayout || !layoutsConfig[currentLayout]) return null;

            // If no prefix, it's a top-level field
            if (!prefix) {
                return layoutsConfig[currentLayout].fields[fieldName] || null;
            }

            // Parse the prefix to navigate through nested structures
            // Example prefix: "text[bloques][0]" for a repeater item
            const prefixParts = prefix.match(/\[([^\]]+)\]/g);
            if (!prefixParts) return null;

            let currentFields = layoutsConfig[currentLayout].fields;

            // Navigate through the nested structure
            for (let i = 0; i < prefixParts.length; i++) {
                const part = prefixParts[i].replace(/[\[\]]/g, '');

                // Skip numeric indices (they're for array items)
                if (!isNaN(part)) continue;

                // Skip 'text' wrapper
                if (part === 'text') continue;

                if (!currentFields[part]) return null;

                // If it's a repeater or group, navigate to its fields
                if (currentFields[part].fields) {
                    currentFields = currentFields[part].fields;
                } else {
                    return null;
                }
            }

            return currentFields[fieldName] || null;
        }

        // Helper function to update field names when reindexing - handles nested repeaters properly
        function updateFieldNames(item, newIndex) {
            const container = item.closest('.repeater-items');
            if (!container) return;

            // Get the field configuration for this repeater
            const repeaterField = container.closest('.repeater-field');
            const fieldName = repeaterField?.querySelector('.add-repeater-item')?.getAttribute('data-field-name');
            const prefix = repeaterField?.querySelector('.add-repeater-item')?.getAttribute('data-prefix');

            if (!fieldName) return;

            // Update all nested field names in this item
            const updateNestedNames = (element, level = 0) => {
                // Update input names
                const inputs = element.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = updateRepeaterIndex(name, fieldName, newIndex, level);
                        input.setAttribute('name', newName);
                    }

                    const id = input.getAttribute('id');
                    if (id) {
                        const newId = id.replace(new RegExp(`_(\\d+)_`), `_${newIndex}_`);
                        input.setAttribute('id', newId);
                    }
                });

                // Update label for attributes
                const labels = element.querySelectorAll('label');
                labels.forEach(label => {
                    const forAttr = label.getAttribute('for');
                    if (forAttr) {
                        const newFor = forAttr.replace(new RegExp(`_(\\d+)_`), `_${newIndex}_`);
                        label.setAttribute('for', newFor);
                    }
                });

                // Update button data attributes
                const buttons = element.querySelectorAll('button[data-field-name], button[data-prefix]');
                buttons.forEach(button => {
                    const dataFieldName = button.getAttribute('data-field-name');
                    const dataPrefix = button.getAttribute('data-prefix');

                    if (dataFieldName && dataFieldName === fieldName) {
                        // This is for the same repeater field, no update needed
                        return;
                    }

                    if (dataPrefix) {
                        const newPrefix = updateRepeaterIndex(dataPrefix, fieldName, newIndex, level);
                        button.setAttribute('data-prefix', newPrefix);
                    }
                });
            };

            updateNestedNames(item);
        }

        // Helper function to update repeater indices in field names
        function updateRepeaterIndex(name, targetFieldName, newIndex, level = 0) {
            // Pattern to match: text[fieldName][currentIndex][...rest...]
            const mainRepeaterPattern = new RegExp(`^(text\\[${targetFieldName}\\]\\[)(\\d+)(\\])`);
            const match = name.match(mainRepeaterPattern);

            if (match) {
                return `${match[1]}${newIndex}${match[3]}${name.substring(match[0].length)}`;
            }

            return name;
        }

        // Helper function to reindex ALL nested repeaters in a container
        function reindexAllNestedRepeaters(container) {
            const repeaterItems = container.querySelectorAll('.repeater-item');
            const nestedRepeaters = new Set();

            // Find all nested repeaters in all items
            repeaterItems.forEach(item => {
                const nestedFields = item.querySelectorAll('.repeater-field');
                nestedFields.forEach(field => {
                    nestedRepeaters.add(field);
                });
            });

            // Reindex each nested repeater separately
            nestedRepeaters.forEach(nestedRepeater => {
                const nestedItems = nestedRepeater.querySelectorAll('.repeater-item');
                nestedItems.forEach((item, index) => {
                    item.setAttribute('data-index', index);
                    const h4 = item.querySelector('h4');
                    if (h4) h4.textContent = `Elemento ${index + 1}`;

                    // Get the nested field name from its add button
                    const addButton = nestedRepeater.querySelector('.add-repeater-item');
                    const nestedFieldName = addButton?.getAttribute('data-field-name');
                    const nestedPrefix = addButton?.getAttribute('data-prefix');

                    if (nestedFieldName) {
                        updateNestedRepeaterFieldNames(item, nestedFieldName, index, nestedPrefix);
                    }
                });
            });
        }

        // Helper function to update field names for nested repeaters
        function updateNestedRepeaterFieldNames(item, fieldName, newIndex, prefix) {
            const inputs = item.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    // For nested repeaters, we need to update their own index
                    // Pattern: text[parentField][parentIndex][fieldName][currentIndex][...]
                    const nestedPattern = new RegExp(`^(text\\[[^\\]]+\\]\\[\\d+\\]\\[${fieldName}\\]\\[)(\\d+)(\\])`);
                    const match = name.match(nestedPattern);

                    if (match) {
                        const newName = `${match[1]}${newIndex}${match[3]}${name.substring(match[0].length)}`;
                        input.setAttribute('name', newName);
                    }
                }

                const id = input.getAttribute('id');
                if (id) {
                    const newId = id.replace(new RegExp(`_${fieldName}_(\\d+)_`), `_${fieldName}_${newIndex}_`);
                    input.setAttribute('id', newId);
                }
            });

            // Update labels
            const labels = item.querySelectorAll('label');
            labels.forEach(label => {
                const forAttr = label.getAttribute('for');
                if (forAttr) {
                    const newFor = forAttr.replace(new RegExp(`_${fieldName}_(\\d+)_`), `_${fieldName}_${newIndex}_`);
                    label.setAttribute('for', newFor);
                }
            });
        }

        // Helper function to reindex repeater items
        function reindexRepeaterItems(container) {
            const items = container.querySelectorAll('.repeater-item');
            items.forEach((item, index) => {
                item.setAttribute('data-index', index);
                const h4 = item.querySelector('h4');
                if (h4) h4.textContent = `Elemento ${index + 1}`;

                updateFieldNames(item, index);
            });

            // Also reindex all nested repeaters after reindexing the main ones
            setTimeout(() => {
                reindexAllNestedRepeaters(container);
            }, 0);
        }

        // Initialize layout on page load
        if (layoutSelect) {
            layoutSelect.addEventListener('change', function() {
                loadLayout(this.value);
            });

            // Load initial layout if editing
            if (isEditing) {
                const currentLayout = '{{ $block->block_type ?? "" }}';
                if (currentLayout) {
                    loadLayout(currentLayout);
                }
            } else if (layoutSelect.value) {
                loadLayout(layoutSelect.value);
            }
        } else if (isEditing) {
            // If editing and no select (layout is locked), load the current layout
            const currentLayout = '{{ $block->block_type ?? "" }}';
            if (currentLayout) {
                loadLayout(currentLayout);
            }
        }

        // File upload preview functionality
        const imageInput = document.getElementById('image-input');
        const browseButton = document.getElementById('browse-files');
        const removeButton = document.getElementById('remove-image');
        const dropzone = document.getElementById('image-dropzone');
        const previewImage = document.getElementById('preview-image');

        if (browseButton && imageInput) {
            browseButton.addEventListener('click', function() {
                imageInput.click();
            });
        }

        if (imageInput && dropzone) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        dropzone.querySelector('.dropzone-content').classList.add('hidden');
                        dropzone.querySelector('.dropzone-preview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Drag and drop functionality
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-primary-400');
            });

            dropzone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('border-primary-400');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-primary-400');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    imageInput.files = files;
                    imageInput.dispatchEvent(new Event('change'));
                }
            });
        }

        if (removeButton) {
            removeButton.addEventListener('click', function() {
                imageInput.value = '';
                dropzone.querySelector('.dropzone-content').classList.remove('hidden');
                dropzone.querySelector('.dropzone-preview').classList.add('hidden');
            });
        }

        // Delete current block image functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-current-block-image')) {
                if (confirm('¿Estás seguro de que quieres eliminar la imagen actual?')) {
                    // Here you would implement the actual deletion logic
                    // For now, just hide the current image section
                    e.target.closest('.mb-4').style.display = 'none';
                }
            }
        });

        // Initial setup for dimension inputs on existing elements
        attachDimensionInputListeners();

        // Global function for deleting current files
        window.deleteCurrentImage = function(fieldId, inputName, fileType) {
            const fileTypeName = fileType === 'image_upload' ? 'imagen' : 'archivo';
            if (confirm(`¿Estás seguro de que quieres eliminar el ${fileTypeName} actual?`)) {
                const uploadInput = document.getElementById('upload_' + fieldId);
                const hiddenInput = document.getElementById('hidden_' + fieldId);
                const currentFileDiv = uploadInput ? uploadInput.closest('.form-group').querySelector('.current-file') : null;
                const previewDiv = uploadInput ? uploadInput.closest('.form-group').querySelector('.file-preview') : null;

                if (hiddenInput) {
                    hiddenInput.value = '';
                    // Also add a delete flag for the backend to handle file deletion
                    const deleteFlag = document.createElement('input');
                    deleteFlag.type = 'hidden';
                    deleteFlag.name = inputName.replace(/\[[^\]]+\]$/, '[_delete]');
                    deleteFlag.value = '1';
                    hiddenInput.parentNode.appendChild(deleteFlag);
                }

                if (currentFileDiv) {
                    currentFileDiv.style.display = 'none';
                }

                if (previewDiv) {
                    previewDiv.style.display = 'none';
                }
            }
        };

        // Global function for handling file upload changes
        window.handleFileUploadChange = function(fieldId, input) {
            if (input.files && input.files[0]) {
                const hiddenInput = document.getElementById('hidden_' + fieldId);
                const currentFileDiv = input.closest('.form-group').querySelector('.current-file');

                // Show preview for the new file
                const file = input.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Create or update preview div
                    let previewDiv = input.closest('.form-group').querySelector('.file-preview');
                    if (!previewDiv) {
                        previewDiv = document.createElement('div');
                        previewDiv.className = 'file-preview mt-3 p-3 border border-gray-200 rounded-lg bg-gray-50';

                        // Add preview image
                        const previewImg = document.createElement('img');
                        previewImg.className = 'max-h-32 w-auto rounded-md shadow-sm';
                        previewImg.src = e.target.result;

                        const fileName = document.createElement('p');
                        fileName.className = 'text-sm text-gray-600 mt-2';
                        fileName.textContent = 'Nuevo archivo: ' + file.name;

                        previewDiv.appendChild(previewImg);
                        previewDiv.appendChild(fileName);

                        input.parentElement.appendChild(previewDiv);
                    } else {
                        const previewImg = previewDiv.querySelector('img');
                        const fileName = previewDiv.querySelector('p');
                        previewImg.src = e.target.result;
                        fileName.textContent = 'Nuevo archivo: ' + file.name;
                        previewDiv.style.display = 'block';
                    }
                };

                reader.readAsDataURL(file);

                // Clear previous file when new file is selected
                if (hiddenInput) {
                    hiddenInput.value = '';
                }

                if (currentFileDiv) {
                    currentFileDiv.style.display = 'none';
                }
            }
        };
    });
</script>

@endpush