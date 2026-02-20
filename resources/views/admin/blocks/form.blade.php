@extends('layouts.visual-editor')

@section('title', isset($block->id) ? 'Editar Bloque' : 'Nuevo Bloque')
@section('editor-title', isset($block->id) ? 'Editando: ' . ($layoutsConfig[$block->block_type]['name'] ?? $block->block_type) : 'Nuevo Bloque')


@section('components-list')
<!-- Available Block Types -->
@foreach($layoutsConfig as $layoutKey => $layoutConfig)
    <div class="component-item" data-block-type="{{ $layoutKey }}" draggable="true">
        <div class="flex items-center">
            <i class="fas fa-cube text-blue-500 mr-3"></i>
            <div>
                <div class="font-medium">{{ $layoutConfig['name'] }}</div>
                <div class="text-xs text-gray-500">{{ $layoutConfig['description'] ?? '' }}</div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('block-settings')
<!-- Block General Settings -->
<div class="space-y-4">
    <div class="form-group">
        <label for="page_id">Página</label>
        {{ html()->select('page_id',
            ['' => 'Sin página específica'] + $pages->pluck('title', 'id')->toArray(),
            old('page_id', $block->page_id ?? request('page_id'))
        )->class('w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500')->id('page_id') }}
    </div>

    <div class="form-group">
        <label>Tipo de bloque</label>
        @if(isset($block->id))
            <div class="flex items-center p-3 bg-gray-100 border border-gray-300 rounded-md">
                <i class="fas fa-lock text-gray-500 mr-3"></i>
                <span class="font-medium text-gray-900">{{ $layoutsConfig[$block->block_type]['name'] ?? $block->block_type }}</span>
            </div>
        @else
            {{ html()->select('block_type',
                ['' => 'Selecciona un tipo'] + collect($layoutsConfig)->sortBy('name')->mapWithKeys(fn($config, $key) => [$key => $config['name']])->toArray(),
                old('block_type', $block->block_type ?? '')
            )->class('w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500')->id('layout-select')->required() }}
        @endif
    </div>

    <div class="form-group">
        {{ html()->hidden('is_active', 0) }}
        <label class="flex items-center space-x-2 cursor-pointer">
            {{ html()->checkbox('is_active', old('is_active', $block->is_active ?? true), 1)
                ->class('form-checkbox text-blue-600 focus:ring-blue-500')
                ->id('is_active') }}
            <span>Bloque activo</span>
        </label>
    </div>
</div>
@endsection

@section('preview-content')
<!-- Block Preview Area -->
<div id="block-preview" class="min-h-screen">
    @if(isset($block->id) && $block->block_type)
        <div class="block-component" data-block-id="{{ $block->id }}" data-block-type="{{ $block->block_type }}">
            @include('blocks.' . $block->block_type, ['block' => $block])
        </div>
    @else
        <!-- Drop zone for new blocks -->
        <div class="drop-zone" id="main-drop-zone">
            <div class="text-center">
                <i class="fas fa-circle-plus text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Arrastra un componente aquí para comenzar</p>
                <p class="text-sm text-gray-400 mt-2">o selecciona un tipo de bloque en la configuración</p>
            </div>
        </div>
    @endif
</div>
@endsection

@section('hidden-form-fields')
@if(isset($block->id))
    {{ html()->hidden('_method', 'PUT') }}
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" id="form-action" value="{{ route('admin.blocks.update', $block->id) }}">
@else
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" id="form-action" value="{{ route('admin.blocks.store') }}">
@endif
@endsection

@section('content')
<div x-data="blockEditor()" x-init="init()">

    <!-- Dynamic Content Fields (will be populated by JavaScript) -->
    <div id="dynamic-fields-container" style="display: none;"></div>

    <!-- Hidden form for collecting data -->
    {{ html()->modelForm(isset($block->id) ? $block : new \App\Models\Block(), 'POST', isset($block->id) ? route('admin.blocks.update', $block->id) : route('admin.blocks.store'))->id('blockForm')->attribute('enctype', 'multipart/form-data')->class('hidden')->open() }}

    @if(isset($block->id))
        {{ html()->hidden('_method', 'PUT') }}
        {{ html()->hidden('block_type', $block->block_type) }}
    @else
        <input type="hidden" name="block_type" id="hidden-block-type" value="">
    @endif

    <input type="hidden" name="page_id" id="hidden-page-id" value="{{ old('page_id', $block->page_id ?? request('page_id')) }}">
    <input type="hidden" name="is_active" id="hidden-is-active" value="{{ old('is_active', $block->is_active ?? 1) }}">
    <input type="hidden" name="sort_order" id="hidden-sort-order" value="{{ old('sort_order', $block->sort_order ?? '') }}">

    <!-- Dynamic fields will be added here by JavaScript -->
    <div id="hidden-dynamic-fields"></div>

    {{ html()->closeModelForm() }}
</div>
@endsection

@push('scripts')

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
        // Visual Editor JavaScript
        function blockEditor() {
            return {
                layoutsConfig: {},
                currentBlockData: {},
                currentBlockType: '',
                selectedBlock: null,
                isEditing: {{ isset($block->id) ? 'true' : 'false' }},

                init() {
                    try {
                        const layoutsScript = document.getElementById('layouts-config');
                        if (layoutsScript) {
                            this.layoutsConfig = JSON.parse(layoutsScript.textContent);
                        }

                        const blockDataScript = document.getElementById('block-data');
                        if (blockDataScript) {
                            this.currentBlockData = JSON.parse(blockDataScript.textContent);
                        }

                        @if(isset($block->id) && $block->block_type)
                            this.currentBlockType = '{{ $block->block_type }}';
                            this.renderBlockPreview();
                        @endif
                    } catch (e) {
                        console.error('Error parsing configuration data:', e);
                    }

                    this.initDragAndDrop();
                    this.initSaveButton();
                    this.initSettingsForm();
                },

                initDragAndDrop() {
                    // Make component items draggable
                    document.querySelectorAll('.component-item').forEach(item => {
                        item.addEventListener('dragstart', (e) => {
                            const blockType = e.target.dataset.blockType;
                            e.dataTransfer.setData('text/plain', blockType);
                            e.target.classList.add('dragging');
                        });

                        item.addEventListener('dragend', (e) => {
                            e.target.classList.remove('dragging');
                        });
                    });

                    // Set up drop zones
                    const dropZone = document.getElementById('main-drop-zone');
                    if (dropZone) {
                        this.setupDropZone(dropZone);
                    }
                },

                setupDropZone(dropZone) {
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.classList.add('drag-over');
                    });

                    dropZone.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('drag-over');
                    });

                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('drag-over');

                        const blockType = e.dataTransfer.getData('text/plain');
                        this.createNewBlock(blockType);
                    });
                },

                createNewBlock(blockType) {
                    if (!this.isEditing) {
                        this.currentBlockType = blockType;
                        document.getElementById('hidden-block-type').value = blockType;

                        // Switch to settings tab to configure the block
                        this.switchToSettingsTab();
                        this.renderBlockPreview();
                        this.generateDynamicFields();
                    }
                },

                renderBlockPreview() {
                    const previewContent = document.getElementById('block-preview');
                    if (!this.currentBlockType || !previewContent) return;

                    const layoutConfig = this.layoutsConfig[this.currentBlockType];
                    if (!layoutConfig) return;

                    // Create a simple preview based on the block type
                    previewContent.innerHTML = `
                        <div class="block-component selected" data-block-type="${this.currentBlockType}">
                            <div class="block-controls">
                                <button class="bg-blue-500 text-white p-1 rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                                <button class="bg-red-500 text-white p-1 rounded text-xs hover:bg-red-600">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <div class="text-center">
                                    <h2 class="text-xl font-bold text-gray-800 mb-2">${layoutConfig.name}</h2>
                                    <p class="text-gray-600">${layoutConfig.description || ''}</p>
                                    <div class="mt-4 p-4 bg-gray-50 rounded">
                                        <p class="text-sm text-gray-500">Vista previa del bloque ${layoutConfig.name}</p>
                                        <p class="text-xs text-gray-400 mt-2">Configure los campos en la barra lateral para ver el contenido</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    this.selectedBlock = previewContent.querySelector('.block-component');
                },

                generateDynamicFields() {
                    if (!this.currentBlockType) return;

                    const layoutConfig = this.layoutsConfig[this.currentBlockType];
                    if (!layoutConfig || !layoutConfig.fields) return;

                    const container = document.getElementById('dynamic-fields-container');
                    container.innerHTML = '';

                    Object.entries(layoutConfig.fields).forEach(([fieldName, fieldConfig]) => {
                        const fieldHtml = this.generateFieldHtml(fieldName, fieldConfig);
                        container.innerHTML += fieldHtml;
                    });

                    // Add hidden form fields
                    this.addHiddenFormFields(layoutConfig.fields);
                },

                generateFieldHtml(fieldName, fieldConfig) {
                    const value = this.currentBlockData[fieldName] || fieldConfig.default || '';
                    const required = fieldConfig.required ? 'required' : '';

                    switch (fieldConfig.type) {
                        case 'text':
                            return `
                                <div class="form-group">
                                    <label for="${fieldName}">${fieldConfig.label}</label>
                                    <input type="text"
                                           id="${fieldName}"
                                           name="${fieldName}"
                                           value="${this.escapeHtml(value)}"
                                           placeholder="${fieldConfig.placeholder || ''}"
                                           ${required}
                                           onchange="this.closest('[x-data]').__x.$data.updateField('${fieldName}', this.value)">
                                </div>
                            `;

                        case 'textarea':
                            return `
                                <div class="form-group">
                                    <label for="${fieldName}">${fieldConfig.label}</label>
                                    <textarea id="${fieldName}"
                                              name="${fieldName}"
                                              rows="3"
                                              placeholder="${fieldConfig.placeholder || ''}"
                                              ${required}
                                              onchange="this.closest('[x-data]').__x.$data.updateField('${fieldName}', this.value)">${this.escapeHtml(value)}</textarea>
                                </div>
                            `;

                        default:
                            return `
                                <div class="form-group">
                                    <label for="${fieldName}">${fieldConfig.label}</label>
                                    <input type="text"
                                           id="${fieldName}"
                                           name="${fieldName}"
                                           value="${this.escapeHtml(value)}"
                                           ${required}
                                           onchange="this.closest('[x-data]').__x.$data.updateField('${fieldName}', this.value)">
                                </div>
                            `;
                    }
                },

                addHiddenFormFields(fields) {
                    const hiddenContainer = document.getElementById('hidden-dynamic-fields');
                    hiddenContainer.innerHTML = '';

                    Object.entries(fields).forEach(([fieldName, fieldConfig]) => {
                        const value = this.currentBlockData[fieldName] || fieldConfig.default || '';
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = fieldName;
                        input.id = `hidden-${fieldName}`;
                        input.value = value;
                        hiddenContainer.appendChild(input);
                    });
                },

                updateField(fieldName, value) {
                    this.currentBlockData[fieldName] = value;
                    const hiddenInput = document.getElementById(`hidden-${fieldName}`);
                    if (hiddenInput) {
                        hiddenInput.value = value;
                    }
                },

                switchToSettingsTab() {
                    // Switch to settings tab in sidebar
                    document.querySelectorAll('.sidebar-tab').forEach(tab => tab.classList.remove('active'));
                    document.querySelector('[data-tab="settings"]').classList.add('active');

                    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
                    document.getElementById('settings-tab').classList.remove('hidden');
                },

                initSaveButton() {
                    document.getElementById('save-block').addEventListener('click', () => {
                        const form = document.getElementById('blockForm');
                        if (form) {
                            form.submit();
                        }
                    });
                },

                initSettingsForm() {
                    // Handle page selection
                    const pageSelect = document.getElementById('page_id');
                    if (pageSelect) {
                        pageSelect.addEventListener('change', (e) => {
                            document.getElementById('hidden-page-id').value = e.target.value;
                        });
                    }

                    // Handle active status
                    const activeCheckbox = document.getElementById('is_active');
                    if (activeCheckbox) {
                        activeCheckbox.addEventListener('change', (e) => {
                            document.getElementById('hidden-is-active').value = e.target.checked ? 1 : 0;
                        });
                    }

                    // Handle block type selection (for new blocks)
                    const layoutSelect = document.getElementById('layout-select');
                    if (layoutSelect) {
                        layoutSelect.addEventListener('change', (e) => {
                            this.currentBlockType = e.target.value;
                            document.getElementById('hidden-block-type').value = e.target.value;
                            this.renderBlockPreview();
                            this.generateDynamicFields();
                        });
                    }
                },

                escapeHtml(unsafe) {
                    if (unsafe === null || unsafe === undefined) return '';
                    return String(unsafe)
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }
            }
        }
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
                    
                    // Remove loading text on error
                    const currentRange = quill.getSelection();
                    if (currentRange) {
                        const text = quill.getText(currentRange.index - '[Subiendo imagen...]'.length, '[Subiendo imagen...]'.length);
                        if (text === '[Subiendo imagen...]') {
                            quill.deleteText(currentRange.index - '[Subiendo imagen...]'.length, '[Subiendo imagen...]'.length);
                        }
                    }
                }
            }

            // Load initial layout if editing or if block type is set
            const initialLayoutType = '{{ old("block_type", $block->block_type ?? "") }}';

            if (initialLayoutType && layoutsConfig[initialLayoutType]) {
                loadLayoutFields(initialLayoutType);
            }

            // Only bind change event if not editing (layout is locked when editing)
            if (layoutSelect && !isEditing) {
                layoutSelect.addEventListener('change', function() {
                    const selectedLayout = this.value;
                    if (selectedLayout) {
                        loadLayoutFields(selectedLayout);
                    } else {
                        hideLayoutDescription();
                        clearDynamicFields();
                        hideImageSection();
                    }
                });
            }

            function loadLayoutFields(layout) {
                const config = layoutsConfig[layout];
                if (!config) return;

                showLayoutDescription(config);
                generateDynamicFields(config.fields, layout);

                if (config.image_required) {
                    showImageSection();
                } else {
                    hideImageSection();
                }
            }

            function showLayoutDescription(config) {
                document.getElementById('layout-name').textContent = config.name;
                document.getElementById('layout-desc').textContent = config.description;
                layoutDescription.classList.remove('hidden');
            }

            function hideLayoutDescription() {
                layoutDescription.classList.add('hidden');
            }

            function showImageSection() {
                imageSection.classList.remove('hidden');
            }

            function hideImageSection() {
                imageSection.classList.add('hidden');
            }

            function clearDynamicFields() {
                // Destroy Quill editors
                Object.values(quillEditors).forEach(editor => {
                    if (editor.container) {
                        editor.container.remove();
                    }
                });
                quillEditors = {};

                dynamicFieldsContainer.innerHTML = '';
            }

            function generateDynamicFields(fields, layout) {
                clearDynamicFields();

                if (!fields || Object.keys(fields).length === 0) {
                    return;
                }

                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden';
                card.innerHTML = `
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-pen-to-square mr-2 text-primary"></i>
                            Configuración del Contenido
                        </h3>
                    </div>
                    <div class="p-6 space-y-6" id="fields-body">
                    </div>
                `;

                const fieldsBody = card.querySelector('#fields-body');

                Object.entries(fields).forEach(([fieldName, fieldConfig]) => {
                    const fieldElement = createField(fieldName, fieldConfig);
                    if (fieldElement) {
                        fieldsBody.appendChild(fieldElement);
                    }
                });

                dynamicFieldsContainer.appendChild(card);

                setTimeout(() => {
                    initializeQuillEditors();
                    initializeSortableContainers();
                }, 100);
            }

            function createField(fieldName, fieldConfig) {
                const fieldContainer = document.createElement('div');

                switch (fieldConfig.type) {
                    case 'text':
                    case 'url':
                    case 'email':
                        fieldContainer.innerHTML = createTextInput(fieldName, fieldConfig);
                        break;
                    case 'textarea':
                        fieldContainer.innerHTML = createTextarea(fieldName, fieldConfig);
                        break;
                    case 'select':
                        fieldContainer.innerHTML = createSelect(fieldName, fieldConfig);
                        break;
                    case 'editor':
                        fieldContainer.innerHTML = createEditor(fieldName, fieldConfig);
                        break;
                    case 'repeater':
                        fieldContainer.innerHTML = createRepeater(fieldName, fieldConfig);
                        break;
                    case 'group':
                        fieldContainer.innerHTML = createGroup(fieldName, fieldConfig);
                        break;
                    case 'image_upload':
                        fieldContainer.innerHTML = createImageUpload(fieldName, fieldConfig);
                        break;
                    case 'video_upload':
                        fieldContainer.innerHTML = createVideoUpload(fieldName, fieldConfig);
                        break;
                    case 'file_upload':
                        fieldContainer.innerHTML = createFileUpload(fieldName, fieldConfig);
                        break;
                    default:
                        return null;
                }

                return fieldContainer;
            }

            function createTextInput(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || config.default || '';
                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <input
                            type="${config.type === 'url' ? 'url' : config.type === 'email' ? 'email' : 'text'}"
                            class="form-input"
                            name="text[${fieldName}]"
                            value="${escapeHtml(currentValue)}"
                            ${config.placeholder ? `placeholder="${config.placeholder}"` : ''}
                            ${config.required ? 'required' : ''}
                        >
                    </div>
                `;
            }

            function createTextarea(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || config.default || '';
                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <textarea
                            class="form-input"
                            name="text[${fieldName}]"
                            rows="4"
                            ${config.placeholder ? `placeholder="${config.placeholder}"` : ''}
                            ${config.required ? 'required' : ''}
                        >${escapeHtml(currentValue)}</textarea>
                    </div>
                `;
            }

            function createSelect(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || config.default || '';
                let optionsHtml = '';
                
                if (config.options) {
                    Object.entries(config.options).forEach(([value, label]) => {
                        const selected = currentValue === value ? 'selected' : '';
                        optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                    });
                }
                
                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <select
                            class="form-select"
                            name="text[${fieldName}]"
                            ${config.required ? 'required' : ''}
                        >
                            ${!config.required ? '<option value="">-- Seleccionar --</option>' : ''}
                            ${optionsHtml}
                        </select>
                    </div>
                `;
            }

            function createEditor(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || config.default || '';
                const editorId = `editor_${fieldName}_${Math.random().toString(36).substr(2, 9)}`;
                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="quill-wrapper">
                            <div id="${editorId}" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 200px;"></div>
                            <input type="hidden" name="text[${fieldName}]" class="quill-content" value="${escapeForHtmlAttribute(currentValue)}" ${config.required ? 'required' : ''} data-editor="${editorId}">
                        </div>
                    </div>
                `;
            }

            function createImageUpload(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || '';

                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="grid grid-cols-1 ${currentValue && isEditing ? 'lg:grid-cols-2' : ''} gap-4">
                            ${currentValue && isEditing ? `
                                <div>
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <img src="${window.assetBaseUrl}${escapeHtml(currentValue)}" alt="Imagen actual" class="mx-auto h-auto max-h-[50vh]">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-file w-full" data-field="${fieldName}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            ` : ''}
                            <div class="dropzone-container">
                            <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors duration-200" data-field="${fieldName}">
                                <div class="dropzone-content">
                                    <i class="fas fa-cloud-arrow-up text-4xl text-gray-400 mx-auto mb-3"></i>
                                    <p class="text-gray-600 mb-2">Arrastra una imagen aquí</p>
                                    <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar archivo</button>
                                </div>
                                <div class="dropzone-preview hidden">
                                    <img class="preview-image mx-auto h-auto max-h-[50vh] mb-3" src="" alt="Preview">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm remove-image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="file" class="hidden image-input" name="upload_${fieldName}" accept="image/*">
                            <input type="hidden" name="text[${fieldName}]" value="${escapeHtml(currentValue)}">
                            ${currentValue && isEditing ? `<input type="hidden" name="delete_${fieldName}" value="0" class="delete-flag">` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            function createVideoUpload(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || '';

                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="grid grid-cols-1 ${currentValue && isEditing ? 'lg:grid-cols-2' : ''} gap-4">
                            ${currentValue && isEditing ? `
                                <div>
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <video controls class="w-full h-auto rounded-md shadow-sm" style="max-height: 200px;">
                                            <source src="${window.assetBaseUrl}${escapeHtml(currentValue)}" type="video/mp4">
                                            Tu navegador no soporta el elemento video.
                                        </video>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-file w-full" data-field="${fieldName}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            ` : ''}
                            <div class="dropzone-container">
                            <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors duration-200" data-field="${fieldName}">
                                <div class="dropzone-content">
                                    <i class="fas fa-video text-4xl text-gray-400 mx-auto mb-3"></i>
                                    <p class="text-gray-600 mb-2">Arrastra un video aquí</p>
                                    <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar archivo</button>
                                    <p class="text-sm text-gray-500 mt-2">Formatos: MP4, MOV, AVI • ${config.max_size || '100MB'} máximo</p>
                                </div>
                                <div class="dropzone-preview hidden">
                                    <video controls class="w-full h-auto rounded-lg shadow-md mb-3" style="max-height: 200px;">
                                        <source class="preview-video" src="" type="video/mp4">
                                    </video>
                                    <div class="flex justify-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm remove-image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="file" class="hidden video-input" name="upload_${fieldName}" accept="${config.accept || 'video/*'}">
                            <input type="hidden" name="text[${fieldName}]" value="${escapeHtml(currentValue)}">
                            ${currentValue ? `<input type="hidden" name="delete_${fieldName}" value="0" class="delete-flag">` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            function createFileUpload(fieldName, config) {
                const currentValue = currentBlockData[fieldName] || '';
                let currentFileHtml = '';

                if (currentValue && isEditing) {
                    const fileName = currentValue.split('/').pop();
                    currentFileHtml = `
                        <div class="mb-4">
                            <label class="form-label">Archivo actual</label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <div class="flex items-center">
                                    <i class="fas fa-file-video text-2xl text-gray-400 mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-700">${fileName}</p>
                                        <a href="${window.assetBaseUrl}${escapeHtml(currentValue)}" target="_blank" class="text-sm text-primary hover:text-primary-700">Ver archivo</a>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-file" data-field="${fieldName}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }

                return `
                    <div>
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        ${currentFileHtml}
                        <div class="dropzone-container">
                            <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors duration-200" data-field="${fieldName}">
                                <div class="dropzone-content">
                                    <i class="fas fa-cloud-arrow-up text-4xl text-gray-400 mx-auto mb-3"></i>
                                    <p class="text-gray-600 mb-2">Arrastra un archivo aquí</p>
                                    <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar archivo</button>
                                    <p class="text-sm text-gray-500 mt-2">Tipos permitidos: ${config.accept || '*'} • ${config.max_size || '100MB'} máximo</p>
                                </div>
                                <div class="dropzone-preview hidden">
                                    <div class="flex items-center p-4 bg-gray-50 rounded-lg mb-3">
                                        <i class="fas fa-file-lines text-2xl text-gray-400 mr-3"></i>
                                        <span class="preview-filename text-gray-700 font-medium"></span>
                                    </div>
                                    <div class="flex justify-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm remove-image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="file" class="hidden file-input" name="upload_${fieldName}" accept="${config.accept || '*'}">
                            <input type="hidden" name="text[${fieldName}]" value="${escapeHtml(currentValue)}">
                            ${currentValue ? `<input type="hidden" name="delete_${fieldName}" value="0" class="delete-flag">` : ''}
                        </div>
                    </div>
                `;
            }

            function createRepeater(fieldName, fieldConfig) {
                let currentItems = currentBlockData[fieldName] || [];
                
                // Fix for [object Object] issue - ensure we have proper array data
                if (typeof currentItems === 'string') {
                    try {
                        currentItems = JSON.parse(currentItems);
                    } catch (e) {
                        console.warn('Could not parse repeater data for field:', fieldName, 'Data:', currentItems);
                        currentItems = [];
                    }
                }
                
                // Ensure it's an array
                if (!Array.isArray(currentItems)) {
                    currentItems = [];
                }
                
                const minItems = fieldConfig.min_items || 1;
                const maxItems = fieldConfig.max_items || 10;

                let itemsHtml = '';
                if (currentItems.length > 0) {
                    currentItems.forEach((item, index) => {
                        itemsHtml += createRepeaterItem(fieldName, fieldConfig, item, index);
                    });
                } else {
                    for (let i = 0; i < minItems; i++) {
                        itemsHtml += createRepeaterItem(fieldName, fieldConfig, {}, i);
                    }
                }

                return `
                    <div>
                        <label class="form-label">
                            ${fieldConfig.label}
                            ${fieldConfig.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="repeater-container border border-gray-200 rounded-lg bg-gray-50 p-4" data-field="${fieldName}" data-min="${minItems}" data-max="${maxItems}">
                            <div class="repeater-items space-y-4">
                                ${itemsHtml}
                            </div>
                            <button type="button" class="btn btn-primary btn-sm add-repeater-item mt-4">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar ${fieldConfig.label.toLowerCase()}
                            </button>
                        </div>
                    </div>
                `;
            }

            function createRepeaterItem(fieldName, config, itemData, index) {
                let fieldsHtml = '';

                if (config.fields) {
                    Object.entries(config.fields).forEach(([subFieldName, subFieldConfig]) => {
                        if (subFieldConfig.type === 'repeater') {
                            // Repeater anidado
                            fieldsHtml += createNestedRepeater(fieldName, index, subFieldName, subFieldConfig, itemData[subFieldName] || []);
                        } else {
                            fieldsHtml += createRepeaterSubField(fieldName, index, subFieldName, subFieldConfig, itemData[subFieldName] || '');
                        }
                    });
                }

                return `<div class="repeater-item bg-white border border-gray-200 rounded-lg" data-index="${index}" x-data="{ expanded: true }"><div class="flex justify-between items-center p-4 cursor-move"><div class="flex items-center space-x-3"><div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600"><i class="fas fa-grip-vertical"></i></div><h4 class="text-sm font-semibold text-gray-700">Elemento ${index + 1}</h4><button type="button" @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 transition-transform duration-200" :class="{ 'rotate-180': expanded }"><i class="fas fa-chevron-down"></i></button></div><div class="flex items-center gap-2"><button type="button" class="btn btn-secondary btn-sm remove-repeater-item"><i class="fas fa-trash"></i></button></div></div><div x-show="expanded" x-collapse class="border-t border-gray-200 p-4"><div class="grid grid-cols-1 md:grid-cols-2 gap-4">${fieldsHtml}</div></div></div>`;
            }

            function createGroup(fieldName, fieldConfig) {
                const groupData = currentBlockData[fieldName] || {};
                let fieldsHtml = '';

                // Generar campos del grupo
                Object.entries(fieldConfig.fields || {}).forEach(([subFieldName, subFieldConfig]) => {
                    const currentValue = groupData[subFieldName] || '';
                    const fieldNameFull = `text[${fieldName}][${subFieldName}]`;
                    const fieldId = `text_${fieldName}_${subFieldName}`;
                    
                    // Handle repeater fields differently
                    if (subFieldConfig.type === 'repeater') {
                        fieldsHtml += createGroupRepeater(fieldName, subFieldName, subFieldConfig, currentValue);
                    } else {
                        fieldsHtml += createGroupSubField(fieldName, subFieldName, subFieldConfig, currentValue);
                    }
                });

                return `
                    <div class="group-container border border-gray-200 rounded-lg bg-gray-50 p-4">
                        <div class="group-header mb-4">
                            <h3 class="text-lg font-semibold text-gray-700">${fieldConfig.label}</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            ${fieldsHtml}
                        </div>
                    </div>
                `;
            }

            function createGroupSubField(parentField, fieldName, config, currentValue) {
                const fieldNameFull = `text[${parentField}][${fieldName}]`;
                const fieldId = `text_${parentField}_${fieldName}`;

                switch (config.type) {
                    case 'text':
                    case 'url':
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="${config.type === 'url' ? 'url' : 'text'}"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                    case 'textarea':
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <textarea
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    rows="4"
                                    ${config.required ? 'required' : ''}
                                >${escapeHtml(currentValue)}</textarea>
                            </div>
                        `;
                    case 'select':
                        let optionsHtml = '';
                        if (config.options) {
                            Object.entries(config.options).forEach(([value, label]) => {
                                const selected = currentValue === value ? 'selected' : '';
                                optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                            });
                        }
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <select
                                    class="form-select"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    ${config.required ? 'required' : ''}
                                >
                                    ${!config.required ? '<option value="">-- Seleccionar --</option>' : ''}
                                    ${optionsHtml}
                                </select>
                            </div>
                        `;
                    case 'editor':
                        const editorId = `editor_${parentField}_${fieldName}_${Math.random().toString(36).substr(2, 9)}`;
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                <div class="quill-wrapper">
                                    <div id="${editorId}" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 200px;"></div>
                                    <input type="hidden" name="${fieldNameFull}" class="quill-content" value="${escapeForHtmlAttribute(currentValue)}" ${config.required ? 'required' : ''} data-editor="${editorId}">
                                </div>
                            </div>
                        `;
                    case 'image_upload':
                        const uploadName = `upload_${parentField}_${fieldName}`;
                        
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                <div class="grid grid-cols-1 ${currentValue && isEditing && currentValue.trim() !== '' ? 'lg:grid-cols-2' : ''} gap-4">
                                    ${currentValue && isEditing && currentValue.trim() !== '' ? `
                                        <div>
                                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                                <img src="${currentValue.startsWith('http') ? currentValue : `${window.assetBaseUrl}${escapeHtml(currentValue)}`}" alt="Imagen actual" class="mx-auto h-auto max-h-[50vh] h-auto">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-repeater-file w-full" data-field="${fieldId}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    ` : ''}
                                    <div class="dropzone-container">
                                        <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                            <div class="dropzone-content">
                                                <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                                <p class="text-sm text-gray-600 mb-2">Imagen aquí</p>
                                                <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                            </div>
                                            <div class="dropzone-preview hidden">
                                                <img class="preview-image mx-auto h-auto max-h-[50vh] mb-2" src="" alt="Preview">
                                                <div class="flex justify-center gap-2">
                                                    <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="file" class="hidden image-input" name="${uploadName}" accept="image/*">
                                        <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                    </div>
                                </div>
                            </div>
                        `;
                    case 'file_upload':
                        const groupFileUploadName = `upload_${parentField}_${fieldName}`;
                        let currentFileHtml = '';

                        if (currentValue && isEditing && currentValue.trim() !== '') {
                            const fileName = currentValue.split('/').pop();
                            currentFileHtml = `
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="form-label text-sm">Archivo actual:</label>
                                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                            <div class="flex items-center">
                                                <i class="fa-solid fa-file-lines mr-3"></i>
                                                <div>
                                                    <p class="font-medium text-gray-700">${fileName}</p>
                                                    <a href="${window.assetBaseUrl}${escapeHtml(currentValue)}" target="_blank" class="text-sm text-primary hover:text-primary-700">Ver archivo</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-repeater-file w-full" data-field="${fieldId}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }

                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                ${currentFileHtml}
                                <div class="dropzone-container">
                                    <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                        <div class="dropzone-content">
                                            <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Archivo aquí</p>
                                            <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                            <p class="text-xs text-gray-500 mt-1">Tipos: ${config.accept || '*'} • ${config.max_size || '100MB'} máx.</p>
                                        </div>
                                        <div class="dropzone-preview hidden">
                                            <div class="flex items-center p-4 bg-gray-50 rounded-lg mb-3">
                                                <i class="fa-solid fa-file-lines mr-3"></i>
                                                <span class="preview-filename text-gray-700 font-medium"></span>
                                            </div>
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="hidden file-input" name="${groupFileUploadName}" accept="${config.accept || '*'}">
                                    <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                </div>
                            </div>
                        `;
                    default:
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="text"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                }
            }

            function createRepeaterSubField(parentField, parentIndex, fieldName, config, currentValue) {
                const fieldNameFull = `text[${parentField}][${parentIndex}][${fieldName}]`;
                const fieldId = `text_${parentField}_${parentIndex}_${fieldName}`;

                switch (config.type) {
                    case 'text':
                    case 'url':
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="${config.type === 'url' ? 'url' : 'text'}"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                    case 'textarea':
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <textarea
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    rows="3"
                                    ${config.required ? 'required' : ''}
                                >${escapeHtml(currentValue)}</textarea>
                            </div>
                        `;
                    case 'editor':
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <div class="quill-wrapper">
                                    <div id="editor-${fieldId}" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 150px;"></div>
                                    <input type="hidden" name="${fieldNameFull}" class="quill-content" value="${escapeForHtmlAttribute(currentValue)}" ${config.required ? 'required' : ''} data-editor="editor-${fieldId}">
                                </div>
                            </div>
                        `;
                    case 'select':
                        let optionsHtml = '';
                        if (config.options) {
                            Object.entries(config.options).forEach(([value, label]) => {
                                const selected = currentValue === value ? 'selected' : '';
                                optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                            });
                        }
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <select
                                    class="form-select"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    ${config.required ? 'required' : ''}
                                >
                                    ${!config.required ? '<option value="">-- Seleccionar --</option>' : ''}
                                    ${optionsHtml}
                                </select>
                            </div>
                        `;
                    case 'image_upload':
                        let currentImageHtml = '';
                        if (currentValue && isEditing && currentValue.trim() !== '') {
                            const imageUrl = currentValue.startsWith('http') ? currentValue : `${window.assetBaseUrl}${escapeHtml(currentValue)}`;
                            currentImageHtml = `
                                <div class="mb-3 current-image-container">
                                    <small class="text-gray-600 block mb-1">Imagen actual:</small>
                                    <img src="${imageUrl}" alt="Imagen actual" class="mx-auto h-auto max-h-[50vh] border border-gray-200 mb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm delete-current-repeater-file block mx-auto" data-field="${fieldId}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }

                        const uploadName = `upload_${parentField}_${parentIndex}_${fieldName}`;

                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                <div class="grid grid-cols-1 ${currentValue && isEditing && currentValue.trim() !== '' ? 'lg:grid-cols-2' : ''} gap-4">
                                    ${currentValue && isEditing && currentValue.trim() !== '' ? `
                                        <div>
                                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                                <img src="${currentValue.startsWith('http') ? currentValue : `${window.assetBaseUrl}${escapeHtml(currentValue)}`}" alt="Imagen actual" class="h-auto max-h-[50vh] mx-auto border border-gray-200">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-repeater-file" data-field="${fieldId}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    ` : ''}
                                    <div class="dropzone-container">
                                    <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                        <div class="dropzone-content">
                                            <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Imagen aquí</p>
                                            <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                        </div>
                                        <div class="dropzone-preview hidden">
                                            <img class="preview-image max-h-[50vh] h-auto mx-auto mb-2" src="" alt="Preview">
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="hidden image-input" name="${uploadName}" accept="image/*">
                                    <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                    </div>
                                </div>
                            </div>
                        `;
                    case 'file_upload':
                        const fileUploadName = `upload_${parentField}_${parentIndex}_${fieldName}`;
                        let currentFileHtml = '';

                        if (currentValue && isEditing && currentValue.trim() !== '') {
                            const fileName = currentValue.split('/').pop();
                            currentFileHtml = `
                                <div class="mb-3 current-file-container">
                                    <small class="text-gray-600 block mb-1">Archivo actual:</small>
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-file-lines mr-3"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">${fileName}</p>
                                                <a href="${window.assetBaseUrl}${escapeHtml(currentValue)}" target="_blank" class="text-sm text-primary hover:text-primary-700">Ver archivo</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-repeater-file" data-field="${fieldId}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }

                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                ${currentFileHtml}
                                <div class="dropzone-container">
                                    <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                        <div class="dropzone-content">
                                            <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Archivo aquí</p>
                                            <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                            <p class="text-xs text-gray-500 mt-1">Tipos: ${config.accept || '*'} • ${config.max_size || '100MB'} máx.</p>
                                        </div>
                                        <div class="dropzone-preview hidden">
                                            <div class="flex items-center p-4 bg-gray-50 rounded-lg mb-3">
                                                <i class="fa-solid fa-file-lines mr-3"></i>
                                                <span class="preview-filename text-gray-700 font-medium"></span>
                                            </div>
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="hidden file-input" name="${fileUploadName}" accept="${config.accept || '*'}">
                                    <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                </div>
                            </div>
                        `;
                    default:
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="text"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                }
            }

            function createGroupRepeater(parentField, fieldName, config, currentValue) {
                // Fix for [object Object] issue - ensure we have proper array data
                let currentItems = currentValue || [];
                
                console.log('createGroupRepeater called:', parentField, fieldName, currentValue);
                
                if (typeof currentItems === 'string') {
                    console.log('Current items is string:', currentItems);
                    if (currentItems.includes('[object Object]')) {
                        console.warn('Found [object Object] string, setting to empty array');
                        currentItems = [];
                    } else {
                        try {
                            currentItems = JSON.parse(currentItems);
                        } catch (e) {
                            console.warn('Could not parse group repeater data for field:', fieldName, 'Data:', currentItems);
                            currentItems = [];
                        }
                    }
                }
                
                // Ensure it's an array
                if (!Array.isArray(currentItems)) {
                    console.log('Converting to array from:', typeof currentItems, currentItems);
                    currentItems = [];
                }
                
                const minItems = config.min_items || 1;
                const maxItems = config.max_items || 10;

                let itemsHtml = '';
                if (currentItems.length > 0) {
                    currentItems.forEach((item, index) => {
                        itemsHtml += createGroupRepeaterItem(parentField, fieldName, config, item, index);
                    });
                } else {
                    for (let i = 0; i < minItems; i++) {
                        itemsHtml += createGroupRepeaterItem(parentField, fieldName, config, {}, i);
                    }
                }

                return `
                    <div class="md:col-span-2">
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="group-repeater-container border border-gray-200 rounded-lg bg-gray-50 p-4" data-parent-field="${parentField}" data-field="${fieldName}" data-min="${minItems}" data-max="${maxItems}">
                            <div class="group-repeater-items space-y-4">
                                ${itemsHtml}
                            </div>
                            <button type="button" class="btn btn-primary btn-sm add-group-repeater-item mt-4">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar ${config.label.toLowerCase()}
                            </button>
                        </div>
                    </div>
                `;
            }

            function createGroupRepeaterItem(parentField, fieldName, config, itemData, index) {
                let fieldsHtml = '';

                if (config.fields) {
                    Object.entries(config.fields).forEach(([subFieldName, subFieldConfig]) => {
                        fieldsHtml += createGroupRepeaterSubField(parentField, fieldName, index, subFieldName, subFieldConfig, itemData[subFieldName] || '');
                    });
                }

                return `
                    <div class="group-repeater-item bg-white border border-gray-200 rounded-lg" data-index="${index}" x-data="{ expanded: false }">
                        <div class="flex justify-between items-center p-4 cursor-move">
                            <div class="flex items-center space-x-3">
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-700">Elemento ${index + 1}</h4>
                                <button 
                                    type="button" 
                                    @click="expanded = !expanded" 
                                    class="text-gray-400 hover:text-gray-600 transition-transform duration-200"
                                    :class="{ 'rotate-180': expanded }"
                                >
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm remove-group-repeater-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div 
                            x-show="expanded" 
                            x-collapse
                            class="border-t border-gray-200 p-4"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${fieldsHtml}
                            </div>
                        </div>
                    </div>
                `;
            }

            function createGroupRepeaterSubField(parentField, fieldName, index, subFieldName, config, currentValue) {
                const fieldNameFull = `text[${parentField}][${fieldName}][${index}][${subFieldName}]`;
                const fieldId = `text_${parentField}_${fieldName}_${index}_${subFieldName}`;

                switch (config.type) {
                    case 'text':
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="text"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                    case 'editor':
                        const editorId = `editor_${fieldId}_${Math.random().toString(36).substr(2, 9)}`;
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                <div class="quill-wrapper">
                                    <div id="${editorId}" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 150px;"></div>
                                    <input type="hidden" name="${fieldNameFull}" class="quill-content" value="${escapeForHtmlAttribute(currentValue)}" ${config.required ? 'required' : ''} data-editor="${editorId}">
                                </div>
                            </div>
                        `;
                    case 'file_upload':
                        const groupRepeaterFileUploadName = `upload_${parentField}_${fieldName}_${index}_${subFieldName}`;
                        let currentFileHtml = '';

                        if (currentValue && isEditing && currentValue.trim() !== '') {
                            const fileName = currentValue.split('/').pop();
                            currentFileHtml = `
                                <div class="mb-3 current-file-container">
                                    <small class="text-gray-600 block mb-1">Archivo actual:</small>
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-file-lines mr-3"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">${fileName}</p>
                                                <a href="${window.assetBaseUrl}${escapeHtml(currentValue)}" target="_blank" class="text-sm text-primary hover:text-primary-700">Ver archivo</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm mt-2 delete-current-repeater-file" data-field="${fieldId}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }

                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                ${currentFileHtml}
                                <div class="dropzone-container">
                                    <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                        <div class="dropzone-content">
                                            <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                            <p class="text-sm text-gray-600 mb-2">Archivo aquí</p>
                                            <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                            <p class="text-xs text-gray-500 mt-1">Tipos: ${config.accept || '*'} • ${config.max_size || '100MB'} máx.</p>
                                        </div>
                                        <div class="dropzone-preview hidden">
                                            <div class="flex items-center p-4 bg-gray-50 rounded-lg mb-3">
                                                <i class="fa-solid fa-file-lines mr-3"></i>
                                                <span class="preview-filename text-gray-700 font-medium"></span>
                                            </div>
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="hidden file-input" name="${groupRepeaterFileUploadName}" accept="${config.accept || '*'}">
                                    <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                </div>
                            </div>
                        `;
                    default:
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="text"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                }
            }

            function createNestedRepeater(parentField, parentIndex, nestedField, config, nestedItems) {
                // Fix for [object Object] issue in nested repeaters
                if (typeof nestedItems === 'string') {
                    try {
                        nestedItems = JSON.parse(nestedItems);
                    } catch (e) {
                        console.warn('Could not parse nested repeater data for field:', nestedField, 'Data:', nestedItems);
                        nestedItems = [];
                    }
                }
                
                // Ensure it's an array
                if (!Array.isArray(nestedItems)) {
                    nestedItems = [];
                }
                
                const minItems = config.min_items || 1;
                const maxItems = config.max_items || 10;

                let nestedItemsHtml = '';
                if (nestedItems.length > 0) {
                    nestedItems.forEach((nestedItem, nestedIndex) => {
                        nestedItemsHtml += createNestedRepeaterItem(parentField, parentIndex, nestedField, config, nestedItem, nestedIndex);
                    });
                } else {
                    for (let i = 0; i < minItems; i++) {
                        nestedItemsHtml += createNestedRepeaterItem(parentField, parentIndex, nestedField, config, {}, i);
                    }
                }

                return `
                    <div class="md:col-span-2">
                        <label class="form-label">
                            ${config.label}
                            ${config.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <div class="nested-repeater-container border border-gray-300 rounded-lg p-4 bg-gray-100"
                             data-parent-field="${parentField}"
                             data-parent-index="${parentIndex}"
                             data-nested-field="${nestedField}"
                             data-min="${minItems}"
                             data-max="${maxItems}">
                            <div class="nested-repeater-items space-y-3">
                                ${nestedItemsHtml}
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm add-nested-item mt-3">
                                <i class="fas fa-plus mr-1"></i>
                                Agregar ${config.label.toLowerCase()}
                            </button>
                        </div>
                    </div>
                `;
            }

            function createNestedRepeaterItem(parentField, parentIndex, nestedField, config, itemData, nestedIndex) {
                let fieldsHtml = '';

                if (config.fields) {
                    Object.entries(config.fields).forEach(([subFieldName, subFieldConfig]) => {
                        fieldsHtml += createNestedSubField(parentField, parentIndex, nestedField, nestedIndex, subFieldName, subFieldConfig, itemData[subFieldName] || '');
                    });
                }

                return `
                    <div class="nested-repeater-item bg-white border border-gray-200 rounded-lg" data-nested-index="${nestedIndex}" x-data="{ expanded: false }">
                        <div class="flex justify-between items-center p-3 cursor-move">
                            <div class="flex items-center space-x-2">
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-grip-vertical text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Elemento ${nestedIndex + 1}</span>
                                <button 
                                    type="button" 
                                    @click="expanded = !expanded" 
                                    class="text-gray-400 hover:text-gray-600 transition-transform duration-200"
                                    :class="{ 'rotate-180': expanded }"
                                >
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm remove-nested-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div
                            x-show="expanded" 
                            x-collapse
                            class="border-t border-gray-200 p-3"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                ${fieldsHtml}
                            </div>
                        </div>
                    </div>`;
            }

            function createNestedSubField(parentField, parentIndex, nestedField, nestedIndex, subFieldName, config, currentValue) {
                // Formato: text[service_groups][0][services][1][image]
                const fieldNameFull = `text[${parentField}][${parentIndex}][${nestedField}][${nestedIndex}][${subFieldName}]`;
                const fieldId = `text_${parentField}_${parentIndex}_${nestedField}_${nestedIndex}_${subFieldName}`;

                switch (config.type) {
                    case 'text':
                    case 'url':
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="${config.type === 'url' ? 'url' : 'text'}"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                    case 'textarea':
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <textarea
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    rows="2"
                                    ${config.required ? 'required' : ''}
                                >${escapeHtml(currentValue)}</textarea>
                            </div>
                        `;
                    case 'select':
                        let optionsHtml = '';
                        if (config.options) {
                            Object.entries(config.options).forEach(([value, label]) => {
                                const selected = currentValue === value ? 'selected' : '';
                                optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                            });
                        }
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <select
                                    class="form-select"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    ${config.required ? 'required' : ''}
                                >
                                    ${!config.required ? '<option value="">-- Seleccionar --</option>' : ''}
                                    ${optionsHtml}
                                </select>
                            </div>
                        `;
                    case 'editor':
                        const editorId = `editor_${parentField}_${parentIndex}_${nestedField}_${nestedIndex}_${subFieldName}_${Math.random().toString(36).substr(2, 9)}`;
                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                <div class="quill-wrapper">
                                    <div id="${editorId}" class="quill-editor bg-white border border-gray-300 rounded-md" style="min-height: 150px;"></div>
                                    <input type="hidden" name="${fieldNameFull}" class="quill-content" value="${escapeForHtmlAttribute(currentValue)}" ${config.required ? 'required' : ''} data-editor="${editorId}">
                                </div>
                            </div>
                        `;
                    case 'image_upload':
                        let currentImageHtml = '';
                        if (currentValue && isEditing && currentValue.trim() !== '') {
                            const imageUrl = currentValue.startsWith('http') ? currentValue : `${window.assetBaseUrl}${escapeHtml(currentValue)}`;
                            currentImageHtml = `
                                <div class="mb-3 current-image-container">
                                    <small class="text-gray-600 block mb-1">Imagen actual:</small>
                                    <img src="${imageUrl}" alt="Imagen actual" class="w-full h-auto rounded border border-gray-200 mb-2" style="max-height: 80px; object-fit: cover;">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm delete-current-repeater-file block mx-auto" data-field="${fieldId}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }

                        const uploadName = `upload_${parentField}_${parentIndex}_${nestedField}_${nestedIndex}_${subFieldName}`;

                        return `
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    ${config.label}
                                    ${config.required ? '<span class="text-red-500">*</span>' : ''}
                                </label>
                                ${currentImageHtml}
                                <div class="dropzone-container">
                                    <div class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-primary-400 transition-colors" data-field="${fieldId}">
                                        <div class="dropzone-content">
                                            <i class="fa-solid fa-cloud-arrow-down mx-auto mb-2"></i>
                                            <p class="text-xs text-gray-600 mb-2">Imagen</p>
                                            <button type="button" class="btn btn-primary btn-sm browse-files">Seleccionar</button>
                                        </div>
                                        <div class="dropzone-preview hidden">
                                            <img class="preview-image h-auto max-h-[50vh] mb-2" src="" alt="Preview" style="max-height: 80px; object-fit: cover;">
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="btn btn-secondary btn-sm remove-image">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="hidden image-input" name="${uploadName}" accept="image/*">
                                    <input type="hidden" name="${fieldNameFull}" value="${escapeHtml(currentValue)}">
                                    </div>
                                </div>
                            </div>
                        `;
                    default:
                        return `
                            <div class="md:col-span-1">
                                <label class="form-label" for="${fieldId}">${config.label}</label>
                                <input
                                    type="text"
                                    class="form-input"
                                    name="${fieldNameFull}"
                                    id="${fieldId}"
                                    value="${escapeHtml(currentValue)}"
                                    ${config.required ? 'required' : ''}
                                >
                            </div>
                        `;
                }
            }

            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                if (typeof text !== 'string') return String(text);
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            async function initializeQuillEditors() {
                const editors = document.querySelectorAll('.quill-editor');
                
                for (const editorElement of editors) {
                    if (editorElement.dataset.initialized) {
                        continue; // Skip already initialized editors
                    }

                    const editorId = editorElement.id;
                    const hiddenInput = document.querySelector(`input[data-editor="${editorId}"]`);

                    if (!hiddenInput) {
                        console.warn('Hidden input not found for editor:', editorId);
                        continue;
                    }

                    try {
                        const quill = await window.initializeQuillEditor(`#${editorId}`, {
                            uploadUrl: '{{ route("admin.blocks.quill-image") }}',
                            modules: {
                                toolbar: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline'],
                                    ['link', 'image'],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    ['clean']
                                ]
                            }
                        });

                        // Set initial content
                        if (hiddenInput.value) {
                            quill.root.innerHTML = hiddenInput.value;
                        }

                        // Update hidden input on content change
                        quill.on('text-change', function() {
                            hiddenInput.value = escapeQuillHtml(quill.root.innerHTML);
                        });

                        // Store the editor instance
                        quillEditors[editorId] = quill;
                        editorElement.dataset.initialized = 'true';
                    } catch (error) {
                        console.error('Failed to initialize Quill editor:', error);
                    }
                }
            }

            async function initializeSortableContainers() {
                if (!window.Sortable) {
                    await window.loadSortable();
                }

                // Initialize sortable for regular repeater containers
                const repeaterContainers = document.querySelectorAll('.repeater-items:not([data-sortable-initialized])');
                repeaterContainers.forEach(container => {
                    new window.Sortable(container, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        chosenClass: 'ring-2',
                        dragClass: 'transform',
                        onEnd: function(evt) {
                            reindexRepeaterItems(container.closest('.repeater-container'));
                        }
                    });
                    container.setAttribute('data-sortable-initialized', 'true');
                });

                // Initialize sortable for group repeater containers
                const groupRepeaterContainers = document.querySelectorAll('.group-repeater-items:not([data-sortable-initialized])');
                groupRepeaterContainers.forEach(container => {
                    new window.Sortable(container, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        chosenClass: 'ring-2',
                        dragClass: 'transform',
                        onEnd: function(evt) {
                            reindexGroupRepeaterItems(container.closest('.group-repeater-container'));
                        }
                    });
                    container.setAttribute('data-sortable-initialized', 'true');
                });

                // Initialize sortable for nested repeater containers
                const nestedRepeaterContainers = document.querySelectorAll('.nested-repeater-items:not([data-sortable-initialized])');
                nestedRepeaterContainers.forEach(container => {
                    new window.Sortable(container, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        chosenClass: 'ring-2',
                        dragClass: 'transform',
                        onEnd: function(evt) {
                            reindexNestedRepeaterItems(container);
                        }
                    });
                    container.setAttribute('data-sortable-initialized', 'true');
                });
            }

            // Event handlers
            document.addEventListener('click', function(e) {
                // Handler específico para botón remove-image (PRIORIDAD MÁXIMA)
                if (e.target.closest('.remove-image')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const button = e.target.closest('.remove-image');
                    const container = button.closest('.dropzone-container');
                    
                    if (container) {
                        // Ocultar preview
                        const preview = container.querySelector('.dropzone-preview');
                        if (preview) {
                            preview.classList.add('hidden');
                        }
                        
                        // Mostrar dropzone
                        const dropzone = container.querySelector('.dropzone');
                        if (dropzone) {
                            dropzone.classList.remove('hidden');
                        }
                        
                        // Mostrar contenido del dropzone
                        const dropzoneContent = container.querySelector('.dropzone-content');
                        if (dropzoneContent) {
                            dropzoneContent.classList.remove('hidden');
                        }
                        
                        // Limpiar input de archivo
                        const fileInput = container.querySelector('input[type="file"]') || 
                                         container.querySelector('.image-input') || 
                                         container.querySelector('.video-input') || 
                                         container.querySelector('.file-input');
                        if (fileInput) {
                            fileInput.value = '';
                        }
                        
                        // Limpiar campo hidden (buscar todos los tipos posibles)
                        const hiddenInputs = container.querySelectorAll('input[type="hidden"]');
                        hiddenInputs.forEach(input => {
                            // Solo limpiar si no es un delete flag
                            if (!input.classList.contains('delete-flag')) {
                                input.value = '';
                            }
                        });
                        
                        // Marcar para borrar si existe delete flag
                        const deleteFlag = container.querySelector('.delete-flag');
                        if (deleteFlag) {
                            deleteFlag.value = '1';
                        }
                        
                        // Limpiar imagen preview si existe
                        const previewImg = container.querySelector('.preview-image, #preview-image');
                        if (previewImg) {
                            previewImg.src = '';
                        }
                        
                        // Limpiar video preview si existe
                        const previewVideo = container.querySelector('.preview-video');
                        if (previewVideo) {
                            previewVideo.src = '';
                        }
                        
                        // Limpiar filename preview si existe
                        const previewFilename = container.querySelector('.preview-filename');
                        if (previewFilename) {
                            previewFilename.textContent = '';
                        }
                    }
                    return; // IMPORTANTE: salir temprano para evitar otros handlers
                }

                // Agregar item de repeater anidado (SIMPLIFICADO)
                if (e.target.classList.contains('add-nested-item') || e.target.closest('.add-nested-item')) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Nested repeater button clicked - SIMPLIFIED');
                    const button = e.target.classList.contains('add-nested-item') ? e.target : e.target.closest('.add-nested-item');
                    
                    // Find the parent repeater item (where this nested repeater lives)
                    const repeaterItem = button.closest('.repeater-item');
                    if (!repeaterItem) {
                        console.error('No parent repeater item found');
                        return;
                    }
                    
                    // Get the parent repeater container to extract field info
                    const parentRepeater = repeaterItem.closest('.repeater-container');
                    if (!parentRepeater) {
                        console.error('No parent repeater container found');
                        return;
                    }
                    
                    // Find the nested items container within this repeater item
                    console.log('Button parent:', button.parentElement);
                    console.log('Button parent HTML:', button.parentElement.outerHTML.substring(0, 500));
                    console.log('Button previousElementSibling:', button.previousElementSibling);
                    
                    // Try different ways to find the container
                    let nestedItemsContainer = button.previousElementSibling;
                    if (!nestedItemsContainer || !nestedItemsContainer.classList.contains('nested-repeater-items')) {
                        // Try looking within the button's parent container
                        nestedItemsContainer = button.parentElement.querySelector('.nested-repeater-items');
                    }
                    if (!nestedItemsContainer) {
                        // Try looking within the repeater item
                        nestedItemsContainer = repeaterItem.querySelector('.nested-repeater-items');
                    }
                    
                    console.log('Found nested items container:', nestedItemsContainer);
                    if (!nestedItemsContainer) {
                        console.error('No nested items container found - creating one');
                        // If no container exists, this means we need to create the nested structure
                        // This suggests the createNestedRepeater function isn't working properly
                        return;
                    }
                    
                    // Get metadata from button or container
                    const buttonContainer = button.parentElement;
                    const parentField = buttonContainer.dataset.parentField || parentRepeater.dataset.field;
                    const parentIndex = parseInt(repeaterItem.dataset.index);
                    const nestedField = buttonContainer.dataset.nestedField || 'galeria'; // fallback
                    
                    console.log('Nested repeater data:', {parentField, parentIndex, nestedField});
                    
                    const currentNestedItems = nestedItemsContainer.querySelectorAll('.nested-repeater-item');
                    const maxItems = 10; // Default max
                    
                    if (currentNestedItems.length < maxItems) {
                        const currentLayoutType = layoutSelect ? layoutSelect.value : initialLayoutType;
                        const fieldConfig = layoutsConfig[currentLayoutType]?.fields[parentField]?.fields[nestedField];
                        if (fieldConfig) {
                            const newNestedIndex = currentNestedItems.length;
                            const newNestedItemHtml = createNestedRepeaterItem(parentField, parentIndex, nestedField, fieldConfig, {}, newNestedIndex);
                            nestedItemsContainer.insertAdjacentHTML('beforeend', newNestedItemHtml);
                            setTimeout(() => {
                                initializeQuillEditors();
                                initializeSortableContainers();
                            }, 100);
                        }
                    }
                    return; // Early return to prevent other handlers
                }

                // Agregar item de repeater principal (SIMPLIFICADO)
                if (e.target.closest('.add-repeater-item') && !e.target.closest('.add-group-repeater-item')) {
                    e.preventDefault();
                    console.log('Regular repeater button clicked - SIMPLIFIED');
                    const button = e.target.closest('.add-repeater-item');
                    console.log('Button:', button);
                    console.log('Button classes:', button.className);
                    
                    // Debug the button's parent hierarchy
                    console.log('Button parent:', button.parentElement);
                    console.log('Button parent classes:', button.parentElement?.className);
                    
                    // Manual search like we did for nested
                    let container = null;
                    let parent = button;
                    let level = 0;
                    
                    while (parent && parent !== document.body && level < 10) {
                        console.log(`Main Level ${level}:`, {
                            element: parent,
                            tagName: parent.tagName,
                            className: parent.className,
                            hasRepeaterClass: parent.classList?.contains('repeater-container'),
                            hasGroupRepeaterClass: parent.classList?.contains('group-repeater-container')
                        });
                        
                        // Check for repeater-container or group-repeater-container classes
                        if (parent.classList?.contains('repeater-container') || parent.classList?.contains('group-repeater-container')) {
                            container = parent;
                            console.log('FOUND MAIN CONTAINER at level', level, ':', container);
                            break;
                        }
                        
                        // Also check for data-field attribute as fallback
                        if (parent.hasAttribute && parent.hasAttribute('data-field')) {
                            console.log(`Level ${level} has data-field:`, parent.getAttribute('data-field'));
                            // If it has data-field but no proper class, it might still be our container
                            if (!container) {
                                container = parent;
                                console.log('FOUND CONTAINER BY data-field at level', level, ':', container);
                            }
                        }
                        parent = parent.parentElement;
                        level++;
                    }
                    
                    console.log('Final main container found:', container);
                    if (!container) {
                        // Last resort: search for any repeater container in the document
                        const allRepeaterContainers = document.querySelectorAll('.repeater-container, .group-repeater-container');
                        console.log('All repeater containers in document:', allRepeaterContainers);
                        if (allRepeaterContainers.length > 0) {
                            // Use the first one found
                            container = allRepeaterContainers[0];
                            console.log('Using first available container:', container);
                        }
                    }
                    
                    if (!container) {
                        console.error('No container found for regular repeater - skipping');
                        return;
                    }
                    const fieldName = container.getAttribute('data-field');
                    const maxItems = parseInt(container.getAttribute('data-max')) || 10;
                    const itemsContainer = container.querySelector('.repeater-items, .group-repeater-items');
                    const currentItems = itemsContainer.querySelectorAll('.repeater-item, .group-repeater-item');

                    if (currentItems.length < maxItems) {
                        const currentLayoutType = layoutSelect ? layoutSelect.value : initialLayoutType;
                        const isGroupRepeater = container.classList.contains('group-repeater-container');
                        
                        if (isGroupRepeater) {
                            // Handle group repeater
                            const parentField = container.getAttribute('data-parent-field');
                            const fieldConfig = layoutsConfig[currentLayoutType]?.fields[parentField]?.fields[fieldName] || {fields: {}};
                            
                            const newIndex = currentItems.length;
                            const newItemHtml = createGroupRepeaterItem(parentField, fieldName, fieldConfig, {}, newIndex);
                            itemsContainer.insertAdjacentHTML('beforeend', newItemHtml);
                            reindexGroupRepeaterItems(container);
                        } else {
                            // Handle normal repeater
                            const fieldConfig = layoutsConfig[currentLayoutType]?.fields[fieldName];
                            if (fieldConfig) {
                                const newIndex = currentItems.length;
                                const newItemHtml = createRepeaterItem(fieldName, fieldConfig, {}, newIndex);
                                // Find the correct items container
                                const repeaterItemsContainer = container.querySelector('.repeater-items');
                                if (repeaterItemsContainer) {
                                    repeaterItemsContainer.insertAdjacentHTML('beforeend', newItemHtml);
                                } else {
                                    itemsContainer.insertAdjacentHTML('beforeend', newItemHtml);
                                }
                                reindexRepeaterItems(container);
                            }
                        }
                        
                        // Initialize Quill editors for any new editor fields
                        setTimeout(() => {
                            initializeQuillEditors();
                            initializeSortableContainers();
                            
                            // Clean up any misplaced items after adding
                            const allContainers = document.querySelectorAll('.repeater-container');
                            allContainers.forEach(container => {
                                const itemsContainer = container.querySelector('.repeater-items');
                                if (itemsContainer) {
                                    const misplacedItems = container.querySelectorAll('.repeater-item');
                                    misplacedItems.forEach(item => {
                                        if (!itemsContainer.contains(item)) {
                                            itemsContainer.appendChild(item);
                                        }
                                    });
                                }
                            });
                            
                            // Clean up nested repeaters too
                            const allNestedContainers = document.querySelectorAll('.nested-repeater-container');
                            allNestedContainers.forEach(container => {
                                const itemsContainer = container.querySelector('.nested-repeater-items');
                                if (itemsContainer) {
                                    const misplacedItems = container.querySelectorAll('.nested-repeater-item');
                                    misplacedItems.forEach(item => {
                                        if (!itemsContainer.contains(item)) {
                                            itemsContainer.appendChild(item);
                                        }
                                    });
                                }
                            });
                        }, 100);
                    }
                }

                // Eliminar item de repeater principal
                if (e.target.closest('.remove-repeater-item')) {
                    e.preventDefault();
                    const button = e.target.closest('.remove-repeater-item');
                    const item = button.closest('.repeater-item, .group-repeater-item');
                    const container = item.closest('.repeater-container, .group-repeater-container');
                    const minItems = parseInt(container.getAttribute('data-min')) || 1;
                    const itemsContainer = container.querySelector('.repeater-items, .group-repeater-items');
                    const currentItems = itemsContainer.querySelectorAll('.repeater-item, .group-repeater-item');

                    if (currentItems.length > minItems) {
                        item.remove();
                        const isGroupRepeater = container.classList.contains('group-repeater-container');
                        if (isGroupRepeater) {
                            reindexGroupRepeaterItems(container);
                        } else {
                            reindexRepeaterItems(container);
                        }
                    }
                }

                // Eliminar item de repeater anidado
                if (e.target.closest('.remove-nested-item')) {
                    e.preventDefault();
                    const button = e.target.closest('.remove-nested-item');
                    const nestedItem = button.closest('.nested-repeater-item');
                    const nestedContainer = nestedItem.closest('.nested-repeater-container');
                    const minItems = parseInt(nestedContainer.getAttribute('data-min'));
                    const nestedItemsContainer = nestedContainer.querySelector('.nested-repeater-items');
                    const currentNestedItems = nestedItemsContainer.querySelectorAll('.nested-repeater-item');

                    if (currentNestedItems.length > minItems) {
                        nestedItem.remove();
                        reindexNestedRepeaterItems(nestedContainer);
                    }
                }

                // Manejar subida de archivos
                if (e.target.classList.contains('image-input') || e.target.classList.contains('video-input') || e.target.classList.contains('file-input')) {
                    const input = e.target;
                    const file = input.files[0];
                    
                    if (file) {
                        const preview = input.parentElement.querySelector('.preview');
                        const hiddenInput = input.parentElement.querySelector('input[type="hidden"]');
                        
                        if (preview && hiddenInput) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                if (input.classList.contains('image-input')) {
                                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="mx-auto max-h-[50vh] h-auto border border-gray-200">`;
                                } else if (input.classList.contains('video-input')) {
                                    preview.innerHTML = `<video src="${e.target.result}" class="mx-auto max-h-[50vh] h-auto border border-gray-200" controls></video>`;
                                }
                            };
                            reader.readAsDataURL(file);
                            hiddenInput.value = file.name; // Temporal, se actualizará en el servidor
                        }
                    }
                }

                // Eliminar archivo actual
                if (e.target.closest('.delete-current-file') || e.target.closest('.delete-current-repeater-file')) {
                    if (confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
                        const button = e.target.closest('.delete-current-file, .delete-current-repeater-file');
                        const fieldId = button.getAttribute('data-field');
                        
                        let hiddenInput;
                        
                        if (fieldId.includes('_')) {
                            // Para repeaters: convertir fieldId a formato de name
                            const parts = fieldId.split('_');
                            if (parts.length === 4) {
                                // Repeater normal: text_parentField_index_fieldName -> text[parentField][index][fieldName]
                                const fieldName = `text[${parts[1]}][${parts[2]}][${parts[3]}]`;
                                hiddenInput = document.querySelector(`input[name="${fieldName}"]`);
                            } else if (parts.length === 6) {
                                // Repeater anidado: text_parentField_parentIndex_nestedField_nestedIndex_subFieldName -> text[parentField][parentIndex][nestedField][nestedIndex][subFieldName]
                                const fieldName = `text[${parts[1]}][${parts[2]}][${parts[3]}][${parts[4]}][${parts[5]}]`;
                                hiddenInput = document.querySelector(`input[name="${fieldName}"]`);
                            }
                        }
                        
                        if (!hiddenInput) {
                            // Fallback: buscar por coincidencia parcial
                            hiddenInput = document.querySelector(`input[name*="${fieldId.replace('text_', '')}"]:not([type="file"])`);
                        }
                        
                        const currentFileContainer = button.closest('.mb-3, .current-image-container, .current-video-container, .current-file-container');
                        
                        if (hiddenInput) {
                            hiddenInput.value = '';
                            console.log('Hidden input cleared:', hiddenInput.name);
                        } else {
                            console.warn('Hidden input not found for fieldId:', fieldId);
                        }
                        
                        if (currentFileContainer) {
                            currentFileContainer.remove();
                            console.log('File container removed');
                        }
                    }
                }

                // Botones de archivos - handler para imágenes, videos y archivos (prioridad alta)
                if (e.target.closest('.browse-files') || e.target.id === 'browse-files') {
                    e.preventDefault();
                    e.stopPropagation();

                    const button = e.target.closest('.browse-files') || e.target;
                    const container = button.closest('.dropzone-container');

                    if (container) {
                        const input = container.querySelector('input[type="file"]') || 
                                     container.querySelector('.image-input') || 
                                     container.querySelector('.video-input') || 
                                     container.querySelector('.file-input');
                        if (input) {
                            input.click();
                            return;
                        }
                    }

                    // Fallback para el botón principal
                    const fallbackInput = document.getElementById('image-input');
                    if (fallbackInput) {
                        fallbackInput.click();
                    }
                    return; // Important: exit early to prevent other handlers
                }

                // Dropzone click handlers (solo si no es un botón browse-files)
                if (e.target.closest('.dropzone') && !e.target.closest('.browse-files')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dropzone = e.target.closest('.dropzone');
                    const fileInput = dropzone.parentElement.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.click();
                    }
                }

                // Group repeater handlers
                if (e.target.closest('.add-group-repeater-item')) {
                    e.preventDefault();
                    console.log('Group repeater button clicked!');
                    const button = e.target.closest('.add-group-repeater-item');
                    console.log('Group button:', button);
                    const container = button.closest('.group-repeater-container');
                    console.log('Group container:', container);
                    const parentField = container.getAttribute('data-parent-field');
                    const fieldName = container.getAttribute('data-field');
                    const maxItems = parseInt(container.getAttribute('data-max')) || 10;
                    const itemsContainer = container.querySelector('.group-repeater-items');
                    const currentItems = itemsContainer.querySelectorAll('.group-repeater-item');

                    if (currentItems.length < maxItems) {
                        const currentLayoutType = layoutSelect ? layoutSelect.value : initialLayoutType;
                        const layoutConfig = layoutsConfig[currentLayoutType];
                        const fieldConfig = layoutConfig?.fields?.[parentField]?.fields?.[fieldName] || {fields: {}};
                        
                        const newIndex = currentItems.length;
                        const newItemHtml = createGroupRepeaterItem(parentField, fieldName, fieldConfig, {}, newIndex);
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = newItemHtml;
                        const newItem = tempDiv.firstElementChild;
                        
                        itemsContainer.appendChild(newItem);
                        reindexGroupRepeaterItems(container);
                        
                        // Initialize any new Quill editors
                        setTimeout(() => {
                            initializeQuillEditors();
                            initializeSortableContainers();
                        }, 100);
                    }
                }

                if (e.target.closest('.remove-group-repeater-item')) {
                    e.preventDefault();
                    const button = e.target.closest('.remove-group-repeater-item');
                    const item = button.closest('.group-repeater-item');
                    const container = item.closest('.group-repeater-container');
                    const minItems = parseInt(container.getAttribute('data-min')) || 1;
                    const itemsContainer = container.querySelector('.group-repeater-items');
                    const currentItems = itemsContainer.querySelectorAll('.group-repeater-item');

                    if (currentItems.length > minItems) {
                        item.remove();
                        reindexGroupRepeaterItems(container);
                    }
                }
            });

            // Handle file changes - usar delegation más específica
            document.addEventListener('change', function(e) {
                // Verificar múltiples clases posibles para el input de archivo
                if (e.target && (e.target.classList.contains('image-input') || 
                                e.target.classList.contains('video-input') || 
                                e.target.classList.contains('file-input') || 
                                e.target.id === 'image-input' || 
                                e.target.type === 'file')) {
                    const input = e.target;
                    const container = input.closest('.dropzone-container');
                    if (container && input.files && input.files.length > 0) {
                        handleFileSelect(container, input.files[0], input);
                    }
                }


            });

            // Drag and drop
            document.addEventListener('dragover', function(e) {
                if (e.target.closest('.dropzone')) {
                    e.preventDefault();
                    const dropzone = e.target.closest('.dropzone');
                    dropzone.classList.add('border-blue-600', 'bg-gray-50');
                }
            });

            document.addEventListener('dragleave', function(e) {
                if (e.target.closest('.dropzone')) {
                    e.preventDefault();
                    const dropzone = e.target.closest('.dropzone');
                    dropzone.classList.remove('border-blue-600', 'bg-gray-50');
                }
            });

            document.addEventListener('drop', function(e) {
                if (e.target.closest('.dropzone')) {
                    e.preventDefault();
                    const dropzone = e.target.closest('.dropzone');
                    const container = dropzone.closest('.dropzone-container');
                    dropzone.classList.remove('border-blue-600', 'bg-gray-50');

                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        handleFileSelect(container, files[0]);
                    }
                }
            });

            function handleFileSelect(container, file, input) {
                console.log('handleFileSelect called:', { container, file, input });

                const isImage = input && (input.classList.contains('image-input') || input.accept === 'image/*');
                const isVideo = input && (input.classList.contains('video-input') || input.accept.includes('video'));
                const isFile = input && (input.classList.contains('file-input') || (!isImage && !isVideo));

                // Validar tipo de archivo
                if (isImage && !file.type.startsWith('image/')) {
                    alert('Solo se permiten archivos de imagen.');
                    return;
                }
                
                if (isVideo && !file.type.startsWith('video/')) {
                    alert('Solo se permiten archivos de video.');
                    return;
                }

                // Validar tamaño de archivo
                let maxSize = 2 * 1024 * 1024; // 2MB por defecto
                if (isVideo) maxSize = 100 * 1024 * 1024; // 50MB para videos
                if (isFile) maxSize = 100 * 1024 * 1024; // 100MB para archivos

                if (file.size > maxSize) {
                    const maxSizeMB = Math.round(maxSize / (1024 * 1024));
                    alert(`El archivo es demasiado grande. Máximo ${maxSizeMB}MB.`);
                    return;
                }

                const fileInput = input || container.querySelector('input[type="file"]');
                const previewImg = container.querySelector('.preview-image') || container.querySelector('#preview-image');
                const previewVideo = container.querySelector('.preview-video');
                const previewFilename = container.querySelector('.preview-filename');
                const dropzoneContent = container.querySelector('.dropzone-content');
                const dropzonePreview = container.querySelector('.dropzone-preview');

                if (!fileInput) {
                    console.error('No input found in container:', container);
                    return;
                }

                // Set files using DataTransfer API si está disponible
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                } catch (e) {
                    console.log('DataTransfer not supported, skipping file assignment');
                }

                // Mostrar preview según el tipo de archivo
                if (isImage && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewImg) {
                            previewImg.src = e.target.result;
                        }
                        showPreview();
                    };
                    reader.onerror = function() {
                        console.error('Error reading image file');
                    };
                    reader.readAsDataURL(file);
                } else if (isVideo && file.type.startsWith('video/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewVideo) {
                            previewVideo.src = e.target.result;
                            const videoElement = previewVideo.closest('video');
                            if (videoElement) {
                                videoElement.load();
                            }
                        }
                        showPreview();
                    };
                    reader.onerror = function() {
                        console.error('Error reading video file');
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Para archivos generales, solo mostrar el nombre
                    if (previewFilename) {
                        previewFilename.textContent = file.name;
                    }
                    showPreview();
                }

                function showPreview() {
                    if (dropzoneContent) {
                        dropzoneContent.classList.add('hidden');
                    }
                    if (dropzonePreview) {
                        dropzonePreview.classList.remove('hidden');
                    }
                    console.log('File preview updated');
                }
            }

            function resetDropzone(container) {
                const input = container.querySelector('.image-input') || 
                             container.querySelector('.video-input') || 
                             container.querySelector('.file-input') ||
                             container.querySelector('input[type="file"]');
                const previewImg = container.querySelector('.preview-image');
                const previewVideo = container.querySelector('.preview-video');
                const previewFilename = container.querySelector('.preview-filename');
                const dropzoneContent = container.querySelector('.dropzone-content');
                const dropzonePreview = container.querySelector('.dropzone-preview');

                if (dropzoneContent) dropzoneContent.classList.remove('hidden');
                if (dropzonePreview) dropzonePreview.classList.add('hidden');
                if (input) input.value = '';
                if (previewImg) previewImg.src = '';
                if (previewVideo) previewVideo.src = '';
                if (previewFilename) previewFilename.textContent = '';
            }

            // Reindexing functions
            function reindexRepeaterItems(container) {
                const itemsContainer = container.querySelector('.repeater-items');
                if (!itemsContainer) return;
                const items = itemsContainer.querySelectorAll(':scope > .repeater-item');
                items.forEach((item, newIndex) => {
                    item.setAttribute('data-index', newIndex);
                    item.querySelector('h4').textContent = `Elemento ${newIndex + 1}`;
                    
                    // Update all field names and IDs within this item
                    const inputs = item.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        if (input.name && input.name.includes('[')) {
                            // Replace the index in the name: text[field][oldIndex] -> text[field][newIndex]
                            input.name = input.name.replace(/\[(\d+)\]/, `[${newIndex}]`);
                        }
                        if (input.id && input.id.includes('_')) {
                            input.id = input.id.replace(/_(\d+)_/, `_${newIndex}_`);
                        }
                    });
                    
                    // Update labels
                    const labels = item.querySelectorAll('label[for]');
                    labels.forEach(label => {
                        if (label.getAttribute('for').includes('_')) {
                            label.setAttribute('for', label.getAttribute('for').replace(/_(\d+)_/, `_${newIndex}_`));
                        }
                    });
                });
            }

            function reindexNestedRepeaterItems(nestedContainer) {
                const nestedItems = nestedContainer.querySelectorAll('.nested-repeater-item');
                nestedItems.forEach((nestedItem, newNestedIndex) => {
                    nestedItem.setAttribute('data-nested-index', newNestedIndex);
                    nestedItem.querySelector('span').textContent = `Elemento ${newNestedIndex + 1}`;
                    
                    // Update all field names and IDs within this nested item
                    const inputs = nestedItem.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        if (input.name && input.name.includes('[')) {
                            // Replace the nested index: text[field][parentIndex][nestedField][oldNestedIndex] -> text[field][parentIndex][nestedField][newNestedIndex]
                            const nameParts = input.name.split('][');
                            if (nameParts.length >= 4) {
                                nameParts[3] = newNestedIndex;
                                input.name = nameParts.join('][');
                            }
                        }
                        if (input.id && input.id.includes('_')) {
                            const idParts = input.id.split('_');
                            if (idParts.length >= 5) {
                                idParts[4] = newNestedIndex;
                                input.id = idParts.join('_');
                            }
                        }
                    });
                });
            }


            function reindexGroupRepeaterItems(container) {
                const items = container.querySelectorAll('.group-repeater-item');
                items.forEach((item, newIndex) => {
                    item.setAttribute('data-index', newIndex);
                    item.querySelector('h4').textContent = `Elemento ${newIndex + 1}`;

                    // Update field names and IDs
                    const inputs = item.querySelectorAll('input, textarea, select');
                    const parentField = container.getAttribute('data-parent-field');
                    const fieldName = container.getAttribute('data-field');
                    
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name && name.includes(`[${fieldName}]`)) {
                            const newName = name.replace(/\[\d+\]/, `[${newIndex}]`);
                            input.setAttribute('name', newName);
                        }
                        
                        const id = input.getAttribute('id');
                        if (id && id.includes(`_${fieldName}_`)) {
                            const newId = id.replace(/_\d+_/, `_${newIndex}_`);
                            input.setAttribute('id', newId);
                        }
                    });

                    // Update labels
                    const labels = item.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr && forAttr.includes(`_${fieldName}_`)) {
                            const newFor = forAttr.replace(/_\d+_/, `_${newIndex}_`);
                            label.setAttribute('for', newFor);
                        }
                    });
                });
            }

            function reindexNestedRepeaterItems(container) {
                const items = container.querySelectorAll('.nested-repeater-item');
                items.forEach((item, newIndex) => {
                    item.setAttribute('data-nested-index', newIndex);
                    item.querySelector('span').textContent = `Elemento ${newIndex + 1}`;

                    // Update field names and IDs for nested repeater items
                    const inputs = item.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            // Replace nested index in patterns like text[field][0][nested][oldIndex]
                            const namePattern = /^(text\[[^\]]+\]\[\d+\]\[[^\]]+\])\[(\d+)\](.*)$/;
                            const match = name.match(namePattern);
                            if (match) {
                                input.setAttribute('name', `${match[1]}[${newIndex}]${match[3]}`);
                            }
                        }
                        
                        const id = input.getAttribute('id');
                        if (id) {
                            // Replace nested index in IDs
                            const idPattern = /^(.+_\d+_.+_)(\d+)(.*)$/;
                            const match = id.match(idPattern);
                            if (match) {
                                input.setAttribute('id', `${match[1]}${newIndex}${match[3]}`);
                            }
                        }
                    });

                    // Update labels
                    const labels = item.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            const idPattern = /^(.+_\d+_.+_)(\d+)(.*)$/;
                            const match = forAttr.match(idPattern);
                            if (match) {
                                label.setAttribute('for', `${match[1]}${newIndex}${match[3]}`);
                            }
                        }
                    });
                });
            }

            // Form submission handler to update Quill editors
            document.getElementById('blockForm').addEventListener('submit', function() {
                // Update all Quill editor contents to hidden inputs before submit
                Object.entries(quillEditors).forEach(([editorId, quill]) => {
                    const hiddenInput = document.querySelector(`input[data-editor="${editorId}"]`);
                    if (hiddenInput && quill) {
                        hiddenInput.value = escapeQuillHtml(quill.root.innerHTML);
                    }
                });
            });

            // Función para limpiar HTML malformado de repeaters al cargar la página
            function fixMalformedRepeaters() {
                // Arreglar repeaters principales
                document.querySelectorAll('.repeater-container').forEach(container => {
                    const itemsContainer = container.querySelector('.repeater-items');
                    if (itemsContainer) {
                        // Encontrar items que están fuera del contenedor
                        const allItems = container.querySelectorAll('.repeater-item');
                        allItems.forEach(item => {
                            if (!itemsContainer.contains(item)) {
                                console.log('Moving misplaced repeater item');
                                itemsContainer.appendChild(item);
                            }
                        });
                        
                        // Reindexar todos los items
                        const items = itemsContainer.querySelectorAll('.repeater-item');
                        items.forEach((item, index) => {
                            item.setAttribute('data-index', index);
                            const h4 = item.querySelector('h4');
                            if (h4) h4.textContent = `Elemento ${index + 1}`;
                        });
                    }
                });
                
                // Arreglar nested repeaters
                document.querySelectorAll('.nested-repeater-container').forEach(container => {
                    const itemsContainer = container.querySelector('.nested-repeater-items');
                    if (itemsContainer) {
                        const allItems = container.querySelectorAll('.nested-repeater-item');
                        allItems.forEach(item => {
                            if (!itemsContainer.contains(item)) {
                                console.log('Moving misplaced nested item');
                                itemsContainer.appendChild(item);
                            }
                        });
                        
                        const items = itemsContainer.querySelectorAll('.nested-repeater-item');
                        items.forEach((item, index) => {
                            item.setAttribute('data-nested-index', index);
                            const span = item.querySelector('span');
                            if (span) span.textContent = `Elemento ${index + 1}`;
                        });
                    }
                });
            }

            // Ejecutar la limpieza al final de DOMContentLoaded
            fixMalformedRepeaters();
        });
    </script>
@endpush