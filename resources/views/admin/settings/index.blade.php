{{-- resources/views/admin/settings/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Configuración</span></li>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configuración del Sitio</h1>
                <p class="text-gray-600 mt-1">Administra las configuraciones generales del sitio web</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            @foreach($settings as $groupName => $groupSettings)
                @php
                    $groupConfig = [
                        'general' => [
                            'title' => 'Configuración General',
                            'description' => 'Información básica del sitio web',
                            'icon' => 'fas fa-gear',
                            'color' => 'blue'
                        ],
                        'imagenes' => [
                            'title' => 'Imágenes del Sitio',
                            'description' => 'Logos, favicon y otras imágenes importantes',
                            'icon' => 'fas fa-images',
                            'color' => 'teal'
                        ],
                        'contacto' => [
                            'title' => 'Información de Contacto',
                            'description' => 'Datos de contacto y direcciones',
                            'icon' => 'fas fa-address-book',
                            'color' => 'green'
                        ],
                        'redes_sociales' => [
                            'title' => 'Redes Sociales',
                            'description' => 'Enlaces a redes sociales',
                            'icon' => 'fas fa-share-nodes',
                            'color' => 'purple'
                        ],
                        'maps' => [
                            'title' => 'Mapas',
                            'description' => 'Configuración de mapas y ubicaciones',
                            'icon' => 'fas fa-location-dot',
                            'color' => 'red'
                        ],
                        'email' => [
                            'title' => 'Configuración de Email',
                            'description' => 'Configuración del servidor de correo',
                            'icon' => 'fas fa-envelope',
                            'color' => 'indigo'
                        ],
                        'files' => [
                            'title' => 'Archivos y Medios',
                            'description' => 'Configuración de subida de archivos',
                            'icon' => 'fas fa-file-upload',
                            'color' => 'yellow'
                        ],
                        'backup' => [
                            'title' => 'Respaldos',
                            'description' => 'Configuración de respaldos automáticos',
                            'icon' => 'fas fa-database',
                            'color' => 'gray'
                        ],
                        'analytics' => [
                            'title' => 'Analytics',
                            'description' => 'Configuración de análisis y seguimiento',
                            'icon' => 'fas fa-chart-bar',
                            'color' => 'orange'
                        ]
                    ];

                    $config = $groupConfig[$groupName] ?? [
                        'title' => ucfirst($groupName),
                        'description' => 'Configuración de ' . strtolower($groupName),
                        'icon' => 'fas fa-gear',
                        'color' => 'gray'
                    ];
                @endphp

                        <!-- Settings Group -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r from-{{ $config['color'] }}-50 to-white px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-{{ $config['color'] }}-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="{{ $config['icon'] }} text-{{ $config['color'] }}-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $config['title'] }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $config['description'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($groupSettings as $setting)
                                @php
                                    $fieldIcons = [
                                        'url' => 'fas fa-link',
                                        'email' => 'fas fa-envelope',
                                        'textarea' => 'fas fa-align-left',
                                        'number' => 'fas fa-hashtag',
                                        'password' => 'fas fa-lock',
                                        'boolean' => 'fas fa-toggle-on',
                                        'select' => 'fas fa-list',
                                        'file' => 'fas fa-file',
                                        'image' => 'fas fa-image',
                                        'text' => 'fas fa-font'
                                    ];
                                    $icon = $fieldIcons[$setting->type] ?? 'fas fa-font';
                                @endphp

                                @if($setting->type === 'image')
                                    <!-- Imagen -->
                                    <div class="md:col-span-2 space-y-3">
                                        <label for="setting_{{ $setting->key }}" class="form-label flex items-center text-sm font-medium text-gray-700">
                                            <i class="{{ $icon }} mr-2 text-gray-500 w-4"></i>
                                            {{ $setting->display_name }}
                                        </label>

                                        <div class="image-field">
                                            <!-- Imagen actual -->
                                            @if($setting->value)
                                                <div id="current_image_{{ $setting->key }}" class="mb-4">
                                                    <div class="relative inline-block">
                                                        <img src="{{ asset($setting->value) }}" alt="{{ $setting->display_name }}" class="h-32 rounded-lg border border-gray-200 shadow-sm">
                                                        <button type="button"
                                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs transition-colors"
                                                                onclick="removeCurrentImage('{{ $setting->key }}')">
                                                            <i class="fas fa-xmark"></i>
                                                        </button>
                                                        <input type="hidden" name="delete_images[{{ $setting->key }}]" value="0" id="delete_{{ $setting->key }}">
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Área de upload -->
                                            <div id="upload_area_{{ $setting->key }}" class="dropzone border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 p-6 text-center {{ $setting->value ? 'hidden' : '' }}">
                                                <div class="dropzone-content">
                                                    <div class="mx-auto w-12 h-12 text-gray-400 mb-3">
                                                        <i class="fas fa-cloud-arrow-up text-3xl"></i>
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        <span class="font-medium text-blue-600 cursor-pointer" onclick="document.getElementById('file_{{ $setting->key }}').click()">Haz clic para subir</span>
                                                        o arrastra y suelta
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        PNG, JPG, GIF, WEBP, SVG hasta 100MB
                                                    </div>
                                                </div>
                                                <input type="file"
                                                       name="images[{{ $setting->key }}]"
                                                       id="file_{{ $setting->key }}"
                                                       accept="image/*"
                                                       class="hidden image-input"
                                                       onchange="previewImage(this, '{{ $setting->key }}')">
                                            </div>

                                            <!-- Vista previa -->
                                            <div id="preview_{{ $setting->key }}" class="dropzone-preview hidden">
                                                <div class="relative inline-block">
                                                    <img id="preview_img_{{ $setting->key }}" class="preview-image max-h-48 max-w-64 object-cover rounded-lg border border-gray-200 shadow-sm mb-3" src="" alt="Vista previa">
                                                </div>
                                                <div class="flex justify-center gap-3">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="removePreview('{{ $setting->key }}')">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        @if($setting->description && $setting->description !== $setting->display_name)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                {{ $setting->description }}
                                            </p>
                                        @endif

                                        @error('images.'.$setting->key)
                                        <div class="form-error text-red-600 text-sm mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                @else
                                    <!-- Otros tipos de campo (código existente) -->
                                    <div class="space-y-2">
                                        <label for="setting_{{ $setting->key }}" class="form-label flex items-center text-sm font-medium text-gray-700">
                                            <i class="{{ $icon }} mr-2 text-gray-500 w-4"></i>
                                            {{ $setting->display_name }}
                                        </label>

                                        @if($setting->type === 'textarea')
                                            <textarea
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-textarea w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    rows="3"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                            >{{ old('settings.'.$setting->key, $setting->value) }}</textarea>

                                        @elseif($setting->type === 'boolean')
                                            <div class="flex items-center">
                                                <input
                                                        type="hidden"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="0"
                                                >
                                                <input
                                                        type="checkbox"
                                                        name="settings[{{ $setting->key }}]"
                                                        id="setting_{{ $setting->key }}"
                                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                                        value="1"
                                                        {{ old('settings.'.$setting->key, $setting->value) == '1' ? 'checked' : '' }}
                                                >
                                                <label for="setting_{{ $setting->key }}" class="ml-2 text-sm font-medium text-gray-900">
                                                    Activado
                                                </label>
                                            </div>

                                        @elseif($setting->type === 'select')
                                            @php
                                                $selectOptions = [
                                                    'smtp_encryption' => [
                                                        'tls' => 'TLS',
                                                        'ssl' => 'SSL',
                                                        'none' => 'Ninguno'
                                                    ],
                                                    'backup_frequency' => [
                                                        'daily' => 'Diario',
                                                        'weekly' => 'Semanal',
                                                        'monthly' => 'Mensual'
                                                    ],
                                                    'language' => [
                                                        'es' => 'Español',
                                                        'en' => 'English'
                                                    ],
                                                    'og_type' => [
                                                        'website' => 'Website',
                                                        'article' => 'Article',
                                                        'business' => 'Business'
                                                    ],
                                                    'twitter_card_type' => [
                                                        'summary' => 'Summary',
                                                        'summary_large_image' => 'Summary Large Image',
                                                        'app' => 'App',
                                                        'player' => 'Player'
                                                    ]
                                                ];
                                                $options = $selectOptions[$setting->key] ?? [];
                                            @endphp

                                            <select
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                            >
                                                @foreach($options as $value => $label)
                                                    <option value="{{ $value }}" {{ old('settings.'.$setting->key, $setting->value) == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        @elseif($setting->type === 'password')
                                            <input
                                                    type="password"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                                    autocomplete="new-password"
                                            >

                                        @elseif($setting->type === 'number')
                                            <input
                                                    type="number"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                            >

                                        @elseif($setting->type === 'email')
                                            <input
                                                    type="email"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                            >

                                        @elseif($setting->type === 'url')
                                            <input
                                                    type="url"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                            >

                                        @else
                                            <input
                                                    type="text"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('settings.'.$setting->key) ? 'border-red-500' : '' }}"
                                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                                    placeholder="{{ $setting->description ?: $setting->display_name }}"
                                            >
                                        @endif

                                        @if($setting->description && $setting->description !== $setting->display_name)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                {{ $setting->description }}
                                            </p>
                                        @endif

                                        @error('settings.'.$setting->key)
                                        <div class="form-error text-red-600 text-sm mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Action Buttons -->
            <div class="flex justify-center pt-6 border-t border-gray-200">
                <button type="submit" class="btn btn-primary shadow-lg px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input, key) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Ocultar área de upload y imagen actual
                    document.getElementById('upload_area_' + key).classList.add('hidden');
                    const currentImage = document.getElementById('current_image_' + key);
                    if (currentImage) {
                        currentImage.classList.add('hidden');
                    }

                    // Mostrar vista previa
                    const preview = document.getElementById('preview_' + key);
                    const previewImg = document.getElementById('preview_img_' + key);
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function removePreview(key) {
            // Limpiar input
            document.getElementById('file_' + key).value = '';

            // Ocultar vista previa
            document.getElementById('preview_' + key).classList.add('hidden');

            // Mostrar área apropiada
            const currentImage = document.getElementById('current_image_' + key);
            const uploadArea = document.getElementById('upload_area_' + key);

            if (currentImage && !currentImage.classList.contains('hidden')) {
                currentImage.classList.remove('hidden');
            } else {
                uploadArea.classList.remove('hidden');
            }
        }

        function removeCurrentImage(key) {
            // Marcar para eliminar
            document.getElementById('delete_' + key).value = '1';
            
            // Ocultar imagen actual
            document.getElementById('current_image_' + key).classList.add('hidden');
            
            // Mostrar área de upload
            document.getElementById('upload_area_' + key).classList.remove('hidden');
        }

        // Drag and drop functionality
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dropzone').forEach(dropzone => {
                const fileInput = dropzone.querySelector('input[type="file"]');
                
                if (!fileInput) return;

                // Extract key from file input id
                const key = fileInput.id.replace('file_', '');

                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.remove('border-gray-300');
                    this.classList.add('border-blue-400', 'bg-blue-50');
                });

                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('border-blue-400', 'bg-blue-50');
                    this.classList.add('border-gray-300');
                });

                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('border-blue-400', 'bg-blue-50');
                    this.classList.add('border-gray-300');

                    const files = e.dataTransfer.files;
                    if (files.length > 0 && files[0].type.startsWith('image/')) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(files[0]);
                        fileInput.files = dataTransfer.files;
                        previewImage(fileInput, key);
                    }
                });
            });
        });
    </script>
@endpush