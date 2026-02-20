<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Editor Visual')</title>

    @stack('styles')
    @vite(['resources/css/admin.css'])

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .visual-editor-header {
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .visual-editor-body {
            margin-top: 60px;
            height: calc(100vh - 60px);
            display: flex;
        }

        .editor-sidebar {
            width: 400px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            flex-shrink: 0;
            position: relative;
            min-width: 300px;
            max-width: 60%;
        }

        .editor-resizer {
            width: 4px;
            background: #e2e8f0;
            cursor: col-resize;
            position: relative;
            flex-shrink: 0;
            transition: background-color 0.2s;
        }

        .editor-resizer:hover {
            background: #3b82f6;
        }

        .editor-resizer::before {
            content: '';
            position: absolute;
            left: -2px;
            right: -2px;
            top: 0;
            bottom: 0;
            background: transparent;
        }

        .editor-resizer.resizing {
            background: #3b82f6;
        }

        .editor-preview {
            flex: 1;
            background: #f0f0f0;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            min-width: 300px;
        }

        .preview-toolbar {
            background: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .preview-frame {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            width: 100%;
            height: calc(100vh - 140px);
        }

        .preview-frame.desktop { width: 100%; }
        .preview-frame.tablet { width: 768px; max-width: 100%; }
        .preview-frame.mobile { width: 375px; max-width: 100%; }

        .preview-frame iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        .sidebar-content {
            padding: 20px;
            height: 100%;
        }

        .preview-mode-btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .preview-mode-btn:hover {
            background: #f9fafb;
        }

        .preview-mode-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* Override admin form styles for sidebar */
        .editor-sidebar .form-input,
        .editor-sidebar .form-select,
        .editor-sidebar .form-textarea {
            font-size: 14px;
        }

        .editor-sidebar .form-group {
            margin-bottom: 16px;
        }

        .editor-sidebar .btn {
            font-size: 14px;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Visual Editor Header -->
    <div class="visual-editor-header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.blocks.index', ['page_id' => $block->page_id ?? null]) }}" class="flex items-center text-white hover:text-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver a Bloques
            </a>
            <div class="h-6 w-px bg-gray-600"></div>
            <h1 class="text-lg font-semibold">@yield('editor-title', 'Editor Visual de Bloques')</h1>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Preview and Save buttons -->
            @if($block->exists)
            <button id="preview-block" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium transition-colors mr-2">
                <i class="fas fa-eye mr-2"></i>
                Previsualizar
            </button>
            @endif
            <button id="save-block" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>
                Guardar
            </button>
        </div>
    </div>

    <!-- Visual Editor Body -->
    <div class="visual-editor-body">
        <!-- Sidebar with Original Form -->
        <div class="editor-sidebar" id="editor-sidebar">
            <!-- Alerts -->
            <div class="p-4">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-circle-check mr-2 text-green-600"></i>
                                <span class="text-green-800 font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-green-800 hover:text-green-900">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>
                                <span class="text-red-800 font-medium">{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-red-800 hover:text-red-900">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform translate-y-2"
                         class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                                    <span class="text-red-800 font-medium">Errores de validación:</span>
                                </div>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button @click="show = false" class="text-red-800 hover:text-red-900 ml-2">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="sidebar-content">
                @yield('form-content')
            </div>
        </div>

        <!-- Resizer -->
        <div class="editor-resizer" id="editor-resizer"></div>

        <!-- Preview Area -->
        <div class="editor-preview">
            <!-- Preview Toolbar -->
            <div class="preview-toolbar">
                <span class="text-sm font-medium text-gray-700 mr-2">Vista previa:</span>
                <button class="preview-mode-btn active" data-mode="desktop">
                    <i class="fas fa-desktop"></i>
                    Escritorio
                </button>
                <button class="preview-mode-btn" data-mode="tablet">
                    <i class="fas fa-tablet-alt"></i>
                    Tablet
                </button>
                <button class="preview-mode-btn" data-mode="mobile">
                    <i class="fas fa-mobile-alt"></i>
                    Móvil
                </button>

                <div class="ml-auto">
                    <button id="refresh-preview" class="preview-mode-btn">
                        <i class="fas fa-rotate"></i>
                        Actualizar
                    </button>
                </div>
            </div>

            <!-- Preview Frame -->
            <div class="preview-frame desktop" id="preview-frame">
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner"></div>
                </div>
                <iframe id="preview-iframe" src="@yield('preview-url')" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    @vite(['resources/js/admin.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewFrame = document.getElementById('preview-frame');
            const previewIframe = document.getElementById('preview-iframe');
            const loadingOverlay = document.getElementById('loading-overlay');
            const saveButton = document.getElementById('save-block');
            const previewButton = document.getElementById('preview-block');
            const refreshButton = document.getElementById('refresh-preview');
            let currentPreviewId = null;

            // Preview mode switching
            document.querySelectorAll('.preview-mode-btn[data-mode]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const mode = this.dataset.mode;

                    // Update active button
                    document.querySelectorAll('.preview-mode-btn[data-mode]').forEach(b =>
                        b.classList.remove('active'));
                    this.classList.add('active');

                    // Update frame class
                    previewFrame.className = `preview-frame ${mode}`;
                });
            });

            // Handle iframe loading
            previewIframe.addEventListener('load', function() {
                loadingOverlay.style.display = 'none';
            });

            // Listen for messages from iframe
            window.addEventListener('message', function(event) {
                // Verify origin for security
                if (event.origin !== window.location.origin) {
                    return;
                }

                if (event.data.type === 'previewUpdated') {
                    // Hide loading overlay when preview is updated
                    loadingOverlay.style.display = 'none';
                } else if (event.data.type === 'iframeLoaded') {
                    // Hide loading overlay when iframe is loaded
                    loadingOverlay.style.display = 'none';
                }
            });

            // Show loading when iframe starts loading
            function showLoading() {
                loadingOverlay.style.display = 'flex';
            }

            // Nueva funcionalidad de previsualización con AJAX
            // Function to process files and save them to sessionStorage for preview
            function processFilesForPreview(formData) {
                try {
                    console.log('Processing files for preview...');

                    // Get existing images from sessionStorage
                    const existingImages = sessionStorage.getItem('previewImages');
                    let imageData = existingImages ? JSON.parse(existingImages) : {};

                    // Process all files in formData
                    for (let [key, value] of formData.entries()) {
                        if (value instanceof File) {
                            console.log('Processing file:', key, value.name);

                            // Convert field name to match backend placeholder format
                            // upload_text[field][index][subfield] -> field_index_subfield
                            const fieldName = convertFieldName(key);
                            console.log('Converted fieldName:', fieldName);

                            // Read file and convert to dataURL
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                // Save to sessionStorage
                                imageData[fieldName] = e.target.result;
                                console.log('Saved to sessionStorage:', fieldName, 'size:', e.target.result.length);

                                // Update sessionStorage
                                sessionStorage.setItem('previewImages', JSON.stringify(imageData));
                                console.log('Updated sessionStorage with', Object.keys(imageData).length, 'images');
                            };
                            reader.readAsDataURL(value);
                        }
                    }
                } catch (error) {
                    console.error('Error processing files for preview:', error);
                }
            }

            // Convert backend field names to frontend field names
            function convertFieldName(backendFieldName) {
                // Examples:
                // upload_text[texto][logo] -> texto_logo
                // upload_text[bloques][0][image_full] -> bloques_0_image_full
                // upload_text[bloques][0][galeria][0][imagen] -> bloques_0_galeria_0_imagen

                const match = backendFieldName.match(/upload_text\[([^\]]+)\](?:\[(\d+)\])?(?:\[([^\]]+)\])?(?:\[(\d+)\])?\[([^\]]+)\]/);
                if (match) {
                    const field = match[1];
                    const index1 = match[2] || '';
                    const nested = match[3] || '';
                    const index2 = match[4] || '';
                    const subfield = match[5] || '';

                    if (nested) {
                        // Nested repeater: field_index1_nested_index2_subfield
                        return `${field}_${index1}_${nested}_${index2}_${subfield}`;
                    } else if (index1) {
                        // Simple repeater: field_index1_subfield
                        return `${field}_${index1}_${subfield}`;
                    } else {
                        // Simple field: field_subfield
                        return `${field}_${subfield}`;
                    }
                }

                // Fallback: remove upload_text prefix and brackets
                return backendFieldName.replace('upload_text[', '').replace(/\]/g, '_').replace(/_$/, '');
            }

            function savePreview() {
                showLoading();

                const form = document.querySelector('form');
                if (!form) {
                    alert('No se encontró el formulario');
                    loadingOverlay.style.display = 'none';
                    return;
                }

                // Actualizar editores Quill antes de guardar
                updateQuillEditors();

                // Crear FormData con todos los datos del formulario
                const formData = new FormData(form);

                // Agregar o actualizar block_id si existe
                @if(isset($block->id))
                    formData.append('block_id', '{{ $block->id }}');
                @endif

                fetch('{{ route("admin.blocks.preview-save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Recargar iframe con datos de preview
                        const blockId = @if(isset($block->id)) '{{ $block->id }}' @else 'null' @endif;
                        previewIframe.src = `{{ route("admin.blocks.iframe-preview") }}/${blockId}`;
                    } else {
                        alert('Error: ' + (data.message || 'Error desconocido'));
                    }
                    loadingOverlay.style.display = 'none';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error de conexión al guardar la previsualización: ' + error.message);
                    loadingOverlay.style.display = 'none';
                });
            }

            function loadPreview() {
                const blockId = @if(isset($block->id)) '{{ $block->id }}' @else 'null' @endif;
                const url = currentPreviewId
                    ? `{{ route("admin.blocks.preview-show") }}/${currentPreviewId}`
                    : `{{ route("admin.blocks.preview-show") }}?block_id=${blockId}`;

                // Recargar el iframe con la nueva previsualización
                previewIframe.src = url;
            }

            function updateQuillEditors() {
                const quillEditors = document.querySelectorAll('.quill-editor');
                quillEditors.forEach(editor => {
                    const textareaId = editor.id.replace('-editor', '');
                    const textarea = document.getElementById(textareaId);
                    if (textarea) {
                        const quillInstance = editor.__quill;
                        if (quillInstance) {
                            textarea.value = quillInstance.root.innerHTML;
                        }
                    }
                });
            }

            // Event listeners para los botones
            previewButton.addEventListener('click', function(e) {
                e.preventDefault();
                savePreview();
            });

            saveButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Guardar directamente
                saveDirectly();
            });

            function saveFromPreview() {
                console.log('Saving from preview...');

                fetch('{{ route("admin.blocks.preview-commit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        preview_id: currentPreviewId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al guardar');
                });
            }

            function saveDirectly() {
                console.log('Saving directly...');

                const form = document.querySelector('form');
                if (form) {
                    updateQuillEditors();
                    form.submit();
                } else {
                    alert('No se encontró el formulario');
                }
            }

            // Manual refresh - ahora usa la previsualización guardada
            refreshButton.addEventListener('click', function() {
                if (currentPreviewId) {
                    loadPreview();
                } else {
                    // Si no hay previsualización, mostrar mensaje
                    previewIframe.src = 'data:text/html;charset=utf-8,' + encodeURIComponent(`
                        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; text-align: center; padding: 20px;">
                            <div>
                                <h3 style="color: #374151; margin-bottom: 10px;">No hay previsualización disponible</h3>
                                <p style="color: #6B7280;">Haga clic en "Previsualizar" para ver el resultado.</p>
                            </div>
                        </div>
                    `);
                    loadingOverlay.style.display = 'none';
                }
            });

            // Resizer functionality
            const editorSidebar = document.getElementById('editor-sidebar');
            const editorResizer = document.getElementById('editor-resizer');
            let isResizing = false;
            let startX = 0;
            let startWidth = 0;

            // Load saved width from localStorage
            const savedWidth = localStorage.getItem('editor-sidebar-width');
            if (savedWidth) {
                editorSidebar.style.width = savedWidth + 'px';
            }

            function startResize(e) {
                isResizing = true;
                startX = e.clientX;
                startWidth = parseInt(window.getComputedStyle(editorSidebar).width, 10);

                editorResizer.classList.add('resizing');
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';

                e.preventDefault();
            }

            function doResize(e) {
                if (!isResizing) return;

                const width = startWidth + (e.clientX - startX);
                const minWidth = 300;
                const maxWidth = window.innerWidth * 0.6;

                if (width >= minWidth && width <= maxWidth) {
                    editorSidebar.style.width = width + 'px';
                }

                e.preventDefault();
            }

            function stopResize() {
                if (!isResizing) return;

                isResizing = false;
                editorResizer.classList.remove('resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';

                // Save width to localStorage
                const currentWidth = parseInt(window.getComputedStyle(editorSidebar).width, 10);
                localStorage.setItem('editor-sidebar-width', currentWidth);
            }

            // Attach resizer events
            editorResizer.addEventListener('mousedown', startResize);
            document.addEventListener('mousemove', doResize);
            document.addEventListener('mouseup', stopResize);

            // Handle window resize
            window.addEventListener('resize', function() {
                const currentWidth = parseInt(window.getComputedStyle(editorSidebar).width, 10);
                const maxWidth = window.innerWidth * 0.6;
                if (currentWidth > maxWidth) {
                    editorSidebar.style.width = maxWidth + 'px';
                    localStorage.setItem('editor-sidebar-width', maxWidth);
                }
            });

            // Clean up temporary URLs when page is unloaded
            window.addEventListener('beforeunload', function() {
                temporaryUrls.forEach(url => {
                    if (url.startsWith('blob:')) {
                        URL.revokeObjectURL(url);
                    }
                });
            });

            // Initialize sessionStorage with existing block images on page load
            initializeExistingImages();

            // Initial load
            showLoading();
        });

        // Function to initialize sessionStorage with existing images from the block
        function initializeExistingImages() {
            try {
                const existingImages = sessionStorage.getItem('previewImages');
                let imageData = existingImages ? JSON.parse(existingImages) : {};

                // Check if we're editing an existing block with images
                @if(isset($block->id) && $block->image_path)
                    // Add main block image
                    imageData['image'] = '{{ asset($block->image_path) }}';
                @endif

                // Check for images in block data
                @if(isset($block->id) && $block->data)
                    const blockData = {!! json_encode($block->getDataAsArray(), JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG) !!};

                    // Look for image fields in block data
                    function findImageFields(data, prefix = '') {
                        for (const [key, value] of Object.entries(data)) {
                            if (typeof value === 'string') {
                                // Check if this looks like an image URL
                                if (value.includes('/storage/') ||
                                    (value.startsWith('http') &&
                                     (value.includes('.jpg') || value.includes('.jpeg') ||
                                      value.includes('.png') || value.includes('.gif') ||
                                      value.includes('.webp')))) {
                                    const fieldName = prefix ? `${prefix}[${key}]` : key;
                                    imageData[fieldName] = value;
                                }
                            } else if (typeof value === 'object' && value !== null) {
                                const newPrefix = prefix ? `${prefix}[${key}]` : key;
                                findImageFields(value, newPrefix);
                            }
                        }
                    }

                    findImageFields(blockData);
                @endif

                // Update sessionStorage if we found any images
                if (Object.keys(imageData).length > 0) {
                    sessionStorage.setItem('previewImages', JSON.stringify(imageData));
                }
            } catch (error) {
                console.error('Error initializing existing images:', error);
            }
        }
    </script>

    @stack('scripts')
</body>
</html>