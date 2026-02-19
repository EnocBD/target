<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\BlockPreview;
use App\Models\Page;
use App\Services\HtmlSanitizerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class BlockController extends Controller
{
    protected HtmlSanitizerService $htmlSanitizer;

    public function __construct(HtmlSanitizerService $htmlSanitizer)
    {
        $this->htmlSanitizer = $htmlSanitizer;
    }

    public function index(Request $request): View
    {
        $query = Block::with(['page', 'parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        // Only show blocks if a page is selected
        if ($request->filled('page_id')) {
            $query->where('page_id', $request->page_id);
        } else {
            // Don't show any blocks if no page is selected
            $query->whereRaw('1 = 0'); // This will return no results
        }

        if ($request->filled('block_type')) {
            $query->where('block_type', $request->block_type);
        }

        $blocks = $query->paginate(15)->appends($request->all());

        $pages = Page::where('is_active', true)
            ->where('slug', '!=', '#')
            ->orderBy('sort_order')
            ->get();

        $layouts = array_keys(config('blocks.layouts', []));

        return view('admin.blocks.index', compact('blocks', 'pages', 'layouts'));
    }

    public function create(Request $request): View
    {
        $block = new Block();

        $pages = Page::orderBy('title')->get();

        $parentBlocks = Block::whereNull('parent_id')
            ->with('page')
            ->orderBy('sort_order')
            ->get();

        $layoutsConfig = config('blocks.layouts', []);

        if ($request->filled('page_id')) {
            $block->page_id = $request->page_id;
        }

        return view('admin.blocks.form-visual', compact('block', 'pages', 'parentBlocks', 'layoutsConfig'));
    }

    public function store(Request $request): RedirectResponse
    {
        $layoutConfig = config("blocks.layouts.{$request->block_type}", []);
        $rules = $this->getValidationRules($request->block_type, $layoutConfig);

        $validated = $request->validate($rules);


        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->handleImageUpload($request->file('image'));
        }

        $textData = $this->processTextFields($request, $layoutConfig);

        // Procesar imágenes de repeaters
        $textData = $this->processRepeaterImages($request, $layoutConfig, $textData);

        // Procesar imágenes de grupos
        $textData = $this->processGroupImages($request, $layoutConfig, $textData);

        // Procesar archivos de campos principales
        foreach ($layoutConfig['fields'] ?? [] as $fieldName => $fieldConfig) {
            if (in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload']) && $request->hasFile("upload_{$fieldName}")) {
                $filePath = $this->handleFileUpload($request->file("upload_{$fieldName}"), $fieldConfig['type']);
                $textData[$fieldName] = $filePath;
            }
        }

        $validated['data'] = $textData;

        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === 0) {
            $maxSortOrder = Block::where('page_id', $validated['page_id'])->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxSortOrder + 1;
        }

        $block = Block::create($validated);

        return redirect()
            ->route('admin.blocks.edit', $block->id)
            ->with('success', 'Bloque creado exitosamente.');
    }

    private function processRepeaterImages(Request $request, array $layoutConfig, array $textData): array
    {
        // Procesar el array upload_text directamente
        $uploadText = $request->file('upload_text');

        if ($uploadText && is_array($uploadText)) {
            $this->processNestedRepeaterFiles($uploadText, $textData, []);
        }

        // También procesar archivos individuales (por si acaso)
        $allFiles = $request->allFiles();
        foreach ($allFiles as $fileName => $file) {
            if ($fileName === 'upload_text') {
                continue; // Ya procesamos este arriba
            }

            // Procesar archivos de repeaters simples: upload_text[field][index][subfield]
            if (preg_match('/^upload_text\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]$/', $fileName, $matches)) {
                $parentField = $matches[1];
                $parentIndex = (int)$matches[2];
                $fieldName = $matches[3];

                $imagePath = $this->handleFileUpload($file, 'image_upload');

                if (!isset($textData[$parentField])) {
                    $textData[$parentField] = [];
                }
                if (!isset($textData[$parentField][$parentIndex])) {
                    $textData[$parentField][$parentIndex] = [];
                }

                $textData[$parentField][$parentIndex][$fieldName] = $imagePath;
            }
            // Procesar archivos de repeaters anidados: upload_text[field][index][nested][nestedIndex][field]
            elseif (preg_match('/^upload_text\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]$/', $fileName, $matches)) {
                $parentField = $matches[1];
                $parentIndex = (int)$matches[2];
                $nestedField = $matches[3];
                $nestedIndex = (int)$matches[4];
                $fieldName = $matches[5];

                $imagePath = $this->handleFileUpload($file, 'image_upload');

                if (!isset($textData[$parentField])) {
                    $textData[$parentField] = [];
                }
                if (!isset($textData[$parentField][$parentIndex])) {
                    $textData[$parentField][$parentIndex] = [];
                }
                if (!isset($textData[$parentField][$parentIndex][$nestedField])) {
                    $textData[$parentField][$parentIndex][$nestedField] = [];
                }
                if (!isset($textData[$parentField][$parentIndex][$nestedField][$nestedIndex])) {
                    $textData[$parentField][$parentIndex][$nestedField][$nestedIndex] = [];
                }

                $textData[$parentField][$parentIndex][$nestedField][$nestedIndex][$fieldName] = $imagePath;
            }
        }

        return $textData;
    }

    /**
     * Procesa recursivamente archivos en arrays anidados de upload_text
     */
    private function processNestedRepeaterFiles($uploadArray, &$textData, $currentPath = []): void
    {
        foreach ($uploadArray as $key => $value) {
            if (is_array($value)) {
                // Continuar recursivamente
                $this->processNestedRepeaterFiles($value, $textData, array_merge($currentPath, [$key]));
            } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                // Es un archivo, procesarlo
                $imagePath = $this->handleFileUpload($value, 'image_upload');

                // Construir la ruta en $textData usando $currentPath
                $this->setNestedValue($textData, $currentPath, $imagePath);
            }
        }
    }

    /**
     * Establece un valor en un array anidado usando un array de claves
     */
    private function setNestedValue(&$array, $keys, $value): void
    {
        // Si no hay claves, no hay dónde establecer el valor
        if (empty($keys)) {
            return;
        }

        $current = &$array;
        $keyCount = count($keys);

        foreach ($keys as $i => $key) {
            // Si es el último elemento, establecemos el valor
            if ($i === $keyCount - 1) {
                $current[$key] = $value;
                return;
            }

            // Si no, creamos/continuamos el array anidado
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
    }

    private function processGroupImages(Request $request, array $layoutConfig, array $textData): array
    {
        $allFiles = $request->allFiles();

        foreach ($allFiles as $fileName => $file) {
            // Procesar archivos de grupos: upload_text[group][field]
            if (preg_match('/^upload_text\[([^\]]+)\]\[([^\]]+)\]$/', $fileName, $matches)) {
                $groupField = $matches[1];
                $fieldName = $matches[2];

                // Verificar si es un campo de grupo
                $groupConfig = $layoutConfig['fields'][$groupField] ?? null;
                if ($groupConfig && $groupConfig['type'] === 'group') {
                    $filePath = $this->handleFileUpload($file, 'image_upload');

                    if (!isset($textData[$groupField])) {
                        $textData[$groupField] = [];
                    }

                    $textData[$groupField][$fieldName] = $filePath;
                }
            }
        }

        return $textData;
    }

    private function processRepeaterFileDeletions(Request $request, array $layoutConfig, array $textData, Block $block): array
    {
        $existingData = $block->getDataAsArray();
        $allInput = $request->all();

        foreach ($allInput as $inputName => $inputValue) {
            // Buscar flags de eliminación para repeaters: text[field][index][_delete]
            if (preg_match('/^text\[([^\]]+)\]\[(\d+)\]\[_delete\]$/', $inputName, $matches) && $inputValue == '1') {
                $fieldName = $matches[1];
                $index = (int)$matches[2];

                $fieldConfig = $layoutConfig['fields'][$fieldName] ?? null;
                if ($fieldConfig && $fieldConfig['type'] === 'repeater') {
                    $existingItem = $existingData[$fieldName][$index] ?? [];

                    // Eliminar archivos asociados con este item del repeater
                    foreach ($fieldConfig['fields'] as $subFieldName => $subFieldConfig) {
                        if (in_array($subFieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                            $filePath = $existingItem[$subFieldName] ?? null;
                            if ($filePath && Storage::disk('public')->exists(ltrim($filePath, '/'))) {
                                Storage::disk('public')->delete(ltrim($filePath, '/'));
                            }

                            // Limpiar el valor en los nuevos datos
                            if (isset($textData[$fieldName][$index][$subFieldName])) {
                                $textData[$fieldName][$index][$subFieldName] = '';
                            }
                        }
                    }
                }
            }

            // Buscar flags de eliminación para grupos: text[group][field][_delete]
            elseif (preg_match('/^text\[([^\]]+)\]\[([^\]]+)\]\[_delete\]$/', $inputName, $matches) && $inputValue == '1') {
                $groupField = $matches[1];
                $fieldName = $matches[2];

                $groupConfig = $layoutConfig['fields'][$groupField] ?? null;
                if ($groupConfig && $groupConfig['type'] === 'group') {
                    $existingGroupData = $existingData[$groupField] ?? [];
                    $filePath = $existingGroupData[$fieldName] ?? null;

                    if ($filePath && Storage::disk('public')->exists(ltrim($filePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($filePath, '/'));
                    }

                    // Limpiar el valor en los nuevos datos
                    if (isset($textData[$groupField][$fieldName])) {
                        $textData[$groupField][$fieldName] = '';
                    }
                }
            }

            // Buscar flags de eliminación para repeaters anidados: text[field][index][nested][nestedIndex][_delete]
            elseif (preg_match('/^text\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]\[(\d+)\]\[_delete\]$/', $inputName, $matches) && $inputValue == '1') {
                $fieldName = $matches[1];
                $index = (int)$matches[2];
                $nestedField = $matches[3];
                $nestedIndex = (int)$matches[4];

                $fieldConfig = $layoutConfig['fields'][$fieldName] ?? null;
                if ($fieldConfig && $fieldConfig['type'] === 'repeater') {
                    $nestedFieldConfig = $fieldConfig['fields'][$nestedField] ?? null;
                    if ($nestedFieldConfig && $nestedFieldConfig['type'] === 'repeater') {
                        $existingNestedItem = $existingData[$fieldName][$index][$nestedField][$nestedIndex] ?? [];

                        // Eliminar archivos asociados con este item del repeater anidado
                        foreach ($nestedFieldConfig['fields'] as $nestedSubFieldName => $nestedSubFieldConfig) {
                            if (in_array($nestedSubFieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                                $filePath = $existingNestedItem[$nestedSubFieldName] ?? null;
                                if ($filePath && Storage::disk('public')->exists(ltrim($filePath, '/'))) {
                                    Storage::disk('public')->delete(ltrim($filePath, '/'));
                                }

                                // Limpiar el valor en los nuevos datos
                                if (isset($textData[$fieldName][$index][$nestedField][$nestedIndex][$nestedSubFieldName])) {
                                    $textData[$fieldName][$index][$nestedField][$nestedIndex][$nestedSubFieldName] = '';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $textData;
    }

    public function edit(Block $block): View
    {
        $pages = Page::orderBy('title')->get();

        $parentBlocks = Block::whereNull('parent_id')
            ->where('id', '!=', $block->id)
            ->with('page')
            ->orderBy('sort_order')
            ->get();

        $layoutsConfig = config('blocks.layouts', []);

        return view('admin.blocks.form-visual', compact('block', 'pages', 'parentBlocks', 'layoutsConfig'));
    }

    public function update(Request $request, Block $block): RedirectResponse
    {
        $layoutConfig = config("blocks.layouts.{$request->block_type}", []);
        $rules = $this->getValidationRules($request->block_type, $layoutConfig, $block->id);

        $validated = $request->validate($rules);

        // Obtener los datos existentes del bloque
        $existingData = $block->getDataAsArray();

        // Procesar campos de texto
        $textData = [];

        if ($request->has('text')) {
            foreach ($request->input('text') as $fieldName => $value) {
                $fieldConfig = $layoutConfig['fields'][$fieldName] ?? null;

                if (!$fieldConfig) continue;

                // Skip file upload fields - they will be processed separately
                if (in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                    continue;
                }

                if ($fieldConfig['type'] === 'repeater' && is_array($value)) {
                    // Procesar repeater de forma simple
                    $textData[$fieldName] = $this->processSimpleRepeater($value, $fieldConfig, $existingData[$fieldName] ?? []);
                } else {
                    // Otros tipos de campo
                    $textData[$fieldName] = $value;
                }
            }
        }

        // Procesar archivos de imagen para repeaters (método simple)
        $textData = $this->processSimpleRepeaterFiles($request, $textData, $request->block_type);

        // Procesar archivos de campos principales
        foreach ($layoutConfig['fields'] ?? [] as $fieldName => $fieldConfig) {
            if (in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                if ($request->has("delete_{$fieldName}") && $request->input("delete_{$fieldName}") == '1') {
                    $oldFilePath = $block->getDataValue($fieldName);
                    if ($oldFilePath && Storage::disk('public')->exists(ltrim($oldFilePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($oldFilePath, '/'));
                    }
                    $textData[$fieldName] = '';
                } elseif ($request->hasFile("upload_{$fieldName}")) {
                    $oldFilePath = $block->getDataValue($fieldName);
                    if ($oldFilePath && Storage::disk('public')->exists(ltrim($oldFilePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($oldFilePath, '/'));
                    }

                    $filePath = $this->handleFileUpload($request->file("upload_{$fieldName}"), $fieldConfig['type']);
                    $textData[$fieldName] = $filePath;
                } elseif ($request->hasFile("upload_text.{$fieldName}")) {
                    // Handle files sent as upload_text[fieldName]
                    $oldFilePath = $block->getDataValue($fieldName);
                    if ($oldFilePath && Storage::disk('public')->exists(ltrim($oldFilePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($oldFilePath, '/'));
                    }

                    $filePath = $this->handleFileUpload($request->file("upload_text.{$fieldName}"), $fieldConfig['type']);
                    $textData[$fieldName] = $filePath;
                } else {
                    // Mantener valor existente si no hay cambios
                    if (!isset($textData[$fieldName]) || ($textData[$fieldName] === '' || $textData[$fieldName] === null)) {
                        $existingFilePath = $block->getDataValue($fieldName);
                        if ($existingFilePath) {
                            $textData[$fieldName] = $existingFilePath;
                        }
                    }
                }
            }
        }

        // Procesar imagen principal del bloque
        if ($request->hasFile('image')) {
            if ($block->image_path) {
                Storage::disk('public')->delete($block->image_path);
            }
            $validated['image_path'] = $this->handleImageUpload($request->file('image'));
        }

        $validated['data'] = $textData;

        // Preservar sort_order original si no se especifica uno nuevo
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $block->sort_order;
        }

        $block->update($validated);

        // Limpiar data_preview al guardar
        $block->data_preview = null;
        $block->save();

        return redirect()
            ->route('admin.blocks.edit', $block->id)
            ->with('success', 'Bloque actualizado exitosamente.');
    }

    /**
     * Procesa repeaters de forma simple y segura
     */
    private function processSimpleRepeater(array $items, array $fieldConfig, array $existingItems): array
    {
        $processedItems = [];

        // Debug: Log los datos de entrada
        \Log::info('processSimpleRepeater - Input:', [
            'items_count' => count($items),
            'existing_count' => count($existingItems),
            'items' => $items,
            'existing_items' => $existingItems
        ]);

        // Crear un mapa de datos existentes para facilitar la búsqueda
        $existingMap = [];
        foreach ($existingItems as $originalIndex => $existingItem) {
            // Crear una clave única para identificar el elemento basado en campos clave
            $key = $this->generateItemKey($existingItem, $fieldConfig);
            if ($key) {
                $existingMap[$key] = $existingItem;
                \Log::info("Generated key for existing item [$originalIndex]: $key");
            }
        }

        foreach ($items as $index => $item) {
            // Saltar elementos marcados para eliminación
            if (isset($item['_delete']) && $item['_delete'] == '1') {
                \Log::info("Skipping item [$index] - marked for deletion");
                continue;
            }

            $processedItem = [];
            $matchingExisting = null;

            // Intentar encontrar un elemento existente que coincida
            $itemKey = $this->generateItemKey($item, $fieldConfig);
            \Log::info("Generated key for new item [$index]: $itemKey");

            if ($itemKey && isset($existingMap[$itemKey])) {
                $matchingExisting = $existingMap[$itemKey];
                \Log::info("Found matching existing item for key: $itemKey");
            }

            foreach ($fieldConfig['fields'] as $subFieldName => $subFieldConfig) {
                // Si hay un valor en el formulario, usarlo (incluso si es null)
                if (array_key_exists($subFieldName, $item)) {
                    // Si es un repeater anidado, procesarlo recursivamente
                    if ($subFieldConfig['type'] === 'repeater' && is_array($item[$subFieldName])) {
                        $nestedExistingItems = $matchingExisting[$subFieldName] ?? [];
                        $processedItem[$subFieldName] = $this->processSimpleRepeater(
                            $item[$subFieldName],
                            $subFieldConfig,
                            $nestedExistingItems
                        );
                        \Log::info("Processed nested repeater [$subFieldName] with " . count($item[$subFieldName]) . " items");
                    } else {
                        $processedItem[$subFieldName] = $item[$subFieldName];
                        \Log::info("Set field [$subFieldName] from form: " . (is_string($item[$subFieldName]) ? substr($item[$subFieldName], 0, 100) : gettype($item[$subFieldName])));
                    }
                } else {
                    // Si no hay valor en el formulario, usar el existente si encontramos coincidencia
                    if ($matchingExisting && array_key_exists($subFieldName, $matchingExisting)) {
                        $existingValue = $matchingExisting[$subFieldName];

                        // Si es un repeater anidado y no hay datos nuevos, mantener el existente
                        if ($subFieldConfig['type'] === 'repeater' && is_array($existingValue)) {
                            $processedItem[$subFieldName] = $existingValue;
                            \Log::info("Preserved nested repeater [$subFieldName] with " . count($existingValue) . " items");
                        } else {
                            $processedItem[$subFieldName] = $existingValue;
                            \Log::info("Preserved field [$subFieldName]: " . (is_string($existingValue) ? substr($existingValue, 0, 100) : gettype($existingValue)));
                        }
                    } else {
                        // Si no hay coincidencia y no hay valor nuevo, usar el valor por defecto
                        $processedItem[$subFieldName] = $subFieldConfig['default'] ?? null;
                        \Log::info("Set default value for field [$subFieldName]");
                    }
                }
            }
            $processedItems[] = $processedItem;
        }

        \Log::info('processSimpleRepeater - Output:', [
            'processed_count' => count($processedItems)
        ]);

        return array_values($processedItems);
    }

    /**
     * Genera una clave única para identificar un item de repeater basado en campos clave
     */
    private function generateItemKey(array $item, array $fieldConfig): string
    {
        $keyParts = [];

        // Priorizar campos específicos para generar claves únicas
        $priorityFields = ['titulo', 'title', 'nombre', 'name', 'subtitulo', 'subtitle'];

        // Primero buscar campos prioritarios
        foreach ($priorityFields as $priorityField) {
            if (isset($fieldConfig['fields'][$priorityField]) && !empty($item[$priorityField])) {
                $keyParts[] = $priorityField . ':' . trim($item[$priorityField]);
                break; // Solo usar el primer campo prioritario encontrado
            }
        }

        // Si no hay campos prioritarios, usar otros campos de texto
        if (empty($keyParts)) {
            foreach ($fieldConfig['fields'] as $fieldName => $subFieldConfig) {
                if (in_array($subFieldConfig['type'], ['text', 'textarea', 'url', 'email']) && !empty($item[$fieldName])) {
                    // Para uploads, usar el nombre del archivo si existe
                    if ($subFieldConfig['type'] === 'image_upload' || $subFieldConfig['type'] === 'file_upload') {
                        $value = is_string($item[$fieldName]) ? basename($item[$fieldName]) : $item[$fieldName];
                    } else {
                        $value = trim($item[$fieldName]);
                    }

                    if ($value && strlen($value) > 0) {
                        $keyParts[] = $fieldName . ':' . $value;
                        break; // Solo usar el primer campo significativo
                    }
                }
            }
        }

        // Si todavía no hay clave, generar una basada en el hash del item completo
        if (empty($keyParts)) {
            $itemHash = md5(serialize($item));
            $keyParts[] = 'hash:' . $itemHash;
        }

        return implode('|', $keyParts);
    }

    /**
     * Procesa archivos de repeaters de forma simple
     */
    private function processSimpleRepeaterFiles(Request $request, array $textData, string $blockType): array
    {
        $allFiles = $request->allFiles();

        \Log::info('processSimpleRepeaterFiles - Input:', [
            'text_data_keys' => array_keys($textData),
            'all_files' => array_keys($allFiles),
            'upload_text_exists' => $request->has('upload_text'),
            'upload_text_type' => gettype($request->file('upload_text'))
        ]);

        // Para el modo de previsualización, necesitamos generar placeholders en lugar de guardar archivos reales
        $isPreviewMode = $request->route()->named('admin.blocks.preview-save');

        // Procesar el array upload_text directamente
        $uploadText = $request->file('upload_text');

        if ($uploadText && is_array($uploadText)) {
            \Log::info('Processing upload_text array', ['upload_text_keys' => array_keys($uploadText)]);
            $this->processNestedUploadFiles($uploadText, $textData, $blockType, [], $isPreviewMode);
        }

        // También procesar archivos individuales (por compatibilidad)
        foreach ($allFiles as $fileName => $file) {
            if ($fileName === 'upload_text') {
                continue; // Ya procesado arriba
            }

            // Buscar archivos de repeaters: upload_text[field][index][subfield]
            if (preg_match('/^upload_text\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]$/', $fileName, $matches)) {
                $parentField = $matches[1];
                $parentIndex = (int)$matches[2];
                $subField = $matches[3];

                // Verificar que el campo es de tipo imagen
                $layoutConfig = config("blocks.layouts.{$blockType}", []);
                $fieldConfig = $layoutConfig['fields'][$parentField]['fields'][$subField] ?? null;

                if ($fieldConfig && in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                    $fieldName = "{$parentField}_{$parentIndex}_{$subField}";

                    if ($isPreviewMode) {
                        // Para preview, generar placeholder
                        $textData[$parentField][$parentIndex][$subField] = "__IMAGE_{$fieldName}__";
                        \Log::info("Generated placeholder for preview: __IMAGE_{$fieldName}__");
                    } else {
                        // Para guardar, procesar el archivo normalmente
                        $filePath = $this->handleFileUpload($file, $fieldConfig['type']);
                        $textData[$parentField][$parentIndex][$subField] = $filePath;
                    }

                    // Asegurarse de que la estructura existe
                    if (!isset($textData[$parentField])) {
                        $textData[$parentField] = [];
                    }
                    if (!isset($textData[$parentField][$parentIndex])) {
                        $textData[$parentField][$parentIndex] = [];
                    }
                }
            }

            // Buscar archivos de repeaters anidados: upload_text[field][index][nested][nestedIndex][subfield]
            if (preg_match('/^upload_text\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]$/', $fileName, $matches)) {
                $parentField = $matches[1];
                $parentIndex = (int)$matches[2];
                $nestedField = $matches[3];
                $nestedIndex = (int)$matches[4];
                $subField = $matches[5];

                // Verificar que el campo es de tipo imagen
                $layoutConfig = config("blocks.layouts.{$blockType}", []);
                $fieldConfig = $layoutConfig['fields'][$parentField]['fields'][$nestedField]['fields'][$subField] ?? null;

                if ($fieldConfig && in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                    $fieldName = "{$parentField}_{$parentIndex}_{$nestedField}_{$nestedIndex}_{$subField}";

                    if ($isPreviewMode) {
                        // Para preview, generar placeholder
                        $textData[$parentField][$parentIndex][$nestedField][$nestedIndex][$subField] = "__IMAGE_{$fieldName}__";
                        \Log::info("Generated nested placeholder for preview: __IMAGE_{$fieldName}__");
                    } else {
                        // Para guardar, procesar el archivo normalmente
                        $filePath = $this->handleFileUpload($file, $fieldConfig['type']);
                        $textData[$parentField][$parentIndex][$nestedField][$nestedIndex][$subField] = $filePath;
                    }

                    // Asegurarse de que la estructura existe
                    if (!isset($textData[$parentField])) {
                        $textData[$parentField] = [];
                    }
                    if (!isset($textData[$parentField][$parentIndex])) {
                        $textData[$parentField][$parentIndex] = [];
                    }
                    if (!isset($textData[$parentField][$parentIndex][$nestedField])) {
                        $textData[$parentField][$parentIndex][$nestedField] = [];
                    }
                    if (!isset($textData[$parentField][$parentIndex][$nestedField][$nestedIndex])) {
                        $textData[$parentField][$parentIndex][$nestedField][$nestedIndex] = [];
                    }
                }
            }
        }

        return $textData;
    }

    /**
     * Obtiene la estructura de un array para debugging
     */
    private function getArrayStructure($array, $depth = 0): array
    {
        $structure = [];
        if ($depth > 3) return ['depth_exceeded' => true];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $structure[$key] = $this->getArrayStructure($value, $depth + 1);
            } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                $structure[$key] = 'UploadedFile: ' . $value->getClientOriginalName();
            } else {
                $structure[$key] = gettype($value);
            }
        }

        return $structure;
    }

    /**
     * Procesa recursivamente archivos en arrays anidados
     */
    private function processNestedUploadFiles($uploadArray, &$textData, string $blockType, array $path, bool $isPreviewMode = false): void
    {
        $layoutConfig = config("blocks.layouts.{$blockType}", []);

        foreach ($uploadArray as $key => $value) {
            if (is_array($value)) {
                // Continuar recursivamente
                $this->processNestedUploadFiles($value, $textData, $blockType, array_merge($path, [$key]), $isPreviewMode);
            } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                // Es un archivo, procesarlo
                $currentPath = array_merge($path, [$key]);

                // Para un path como ['bloques', 0, 'imagen'] (repeater simple)
                if (count($currentPath) === 3) {
                    $fieldName = $currentPath[0];
                    $index = (int)$currentPath[1];
                    $subField = $currentPath[2];

                    // Verificar configuración del campo
                    $fieldConfig = $layoutConfig['fields'][$fieldName]['fields'][$subField] ?? null;

                    if ($fieldConfig && in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                        $placeholderName = "{$fieldName}_{$index}_{$subField}";

                        // Asegurarse de que la estructura existe
                        if (!isset($textData[$fieldName])) {
                            $textData[$fieldName] = [];
                        }
                        if (!isset($textData[$fieldName][$index])) {
                            $textData[$fieldName][$index] = [];
                        }

                        if ($isPreviewMode) {
                            // Para preview, generar placeholder
                            $textData[$fieldName][$index][$subField] = "__IMAGE_{$placeholderName}__";
                            \Log::info("Generated nested placeholder for preview: __IMAGE_{$placeholderName}__");
                        } else {
                            // Para guardar, procesar el archivo normalmente
                            $filePath = $this->handleFileUpload($value, $fieldConfig['type']);
                            $textData[$fieldName][$index][$subField] = $filePath;
                        }
                    }
                }
                // Para un path como ['bloques', 0, 'galeria', 1, 'imagen'] (repeater anidado)
                elseif (count($currentPath) === 5) {
                    $fieldName = $currentPath[0];
                    $index = (int)$currentPath[1];
                    $nestedField = $currentPath[2];
                    $nestedIndex = (int)$currentPath[3];
                    $subField = $currentPath[4];

                    // Verificar configuración del campo anidado
                    $fieldConfig = $layoutConfig['fields'][$fieldName]['fields'][$nestedField]['fields'][$subField] ?? null;

                    if ($fieldConfig && in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                        $placeholderName = "{$fieldName}_{$index}_{$nestedField}_{$nestedIndex}_{$subField}";

                        // Asegurarse de que la estructura existe
                        if (!isset($textData[$fieldName])) {
                            $textData[$fieldName] = [];
                        }
                        if (!isset($textData[$fieldName][$index])) {
                            $textData[$fieldName][$index] = [];
                        }
                        if (!isset($textData[$fieldName][$index][$nestedField])) {
                            $textData[$fieldName][$index][$nestedField] = [];
                        }
                        if (!isset($textData[$fieldName][$index][$nestedField][$nestedIndex])) {
                            $textData[$fieldName][$index][$nestedField][$nestedIndex] = [];
                        }

                        if ($isPreviewMode) {
                            // Para preview, generar placeholder
                            $textData[$fieldName][$index][$nestedField][$nestedIndex][$subField] = "__IMAGE_{$placeholderName}__";
                            \Log::info("Generated nested placeholder for preview: __IMAGE_{$placeholderName}__");
                        } else {
                            // Para guardar, procesar el archivo normalmente
                            $filePath = $this->handleFileUpload($value, $fieldConfig['type']);
                            $textData[$fieldName][$index][$nestedField][$nestedIndex][$subField] = $filePath;
                        }
                    }
                }
            }
        }
    }



    public function destroy(Block $block): RedirectResponse
    {
        $pageId = $block->page_id;

        if ($block->image_path) {
            Storage::disk('public')->delete($block->image_path);
        }

        $layoutConfig = config("blocks.layouts.{$block->block_type}", []);
        foreach ($layoutConfig['fields'] ?? [] as $fieldName => $fieldConfig) {
            if (in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                $filePath = $block->getDataValue($fieldName);
                if ($filePath && Storage::disk('public')->exists(ltrim($filePath, '/'))) {
                    Storage::disk('public')->delete(ltrim($filePath, '/'));
                }
            }
        }

        $block->delete();

        return redirect()
            ->route('admin.blocks.index', ['page_id' => $pageId])
            ->with('success', 'Bloque eliminado exitosamente.');
    }

    public function uploadQuillImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,web|max:5120' // 5MB max
        ]);

        try {
            $file = $request->file('image');
            $filename = uniqid('quill_') . '.' . $file->getClientOriginalExtension();
            $path = 'images/quill/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            // Create directory if it doesn't exist
            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Use Intervention Image v3 to resize and save
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );

            $image = $manager->read($file->getPathname());

            // Resize to max width 1200px while maintaining aspect ratio
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            // Save the processed image
            $image->save($fullPath);

            // Return the URL for Quill
            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePosition(Request $request)
    {
        $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|exists:blocks,id',
            'blocks.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->blocks as $blockData) {
            Block::where('id', $blockData['id'])
                ->update(['sort_order' => $blockData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    // NEW METHOD: Update order for sortable functionality
    public function updateOrder(Request $request)
    {
        $data = $request->all(); // [{id, sort_order}, ...]
        foreach($data as $item) {
            Block::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function duplicate(Block $block): RedirectResponse
    {
        $newBlock = $block->replicate();
        $newBlock->sort_order = Block::where('page_id', $block->page_id)->max('sort_order') + 1;

        if ($block->image_path) {
            $extension = pathinfo($block->image_path, PATHINFO_EXTENSION);
            $newImagePath = 'blocks/' . uniqid() . '.' . $extension;
            Storage::disk('public')->copy($block->image_path, $newImagePath);
            $newBlock->image_path = $newImagePath;
        }

        $newBlock->save();

        return redirect()
            ->route('admin.blocks.index', ['page_id' => $newBlock->page_id])
            ->with('success', 'Bloque duplicado exitosamente.');
    }

    public static function renderBlock(Block $block): string
    {
        $layout = $block->block_type;
        $viewPath = "blocks.{$layout}";

        if (!view()->exists($viewPath)) {
            return "<!-- Block template '{$viewPath}' not found -->";
        }

        try {
            $blockStyles = $block->getComputedStyles();
            $content = view($viewPath, [
                'block' => $block,
                'text' => $block->data,
                'data' => $block->data,
            ])->render();

            // Wrap content with styles if there are any
            if ($blockStyles) {
                return '<div style="' . htmlspecialchars($blockStyles, ENT_QUOTES, 'UTF-8') . '" class="block-wrapper block-' . $block->block_type . '">' . $content . '</div>';
            }

            return $content;
        } catch (\Exception $e) {
            return "<!-- Error rendering block: {$e->getMessage()} -->";
        }
    }

    private function getValidationRules(string $layout, array $layoutConfig, ?int $blockId = null): array
    {
        $rules = [
            'page_id' => 'nullable|exists:pages,id',
            'parent_id' => 'nullable|exists:blocks,id',
            'block_type' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|mimes:jpeg,jpg,png,webp,svg|max:2048',
            'styles' => 'nullable|array',
            'styles.*' => 'nullable|string'
        ];

        $textFields = $layoutConfig['fields'] ?? [];
        foreach ($textFields as $field => $config) {
            $fieldRules = [];

            if ($config['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($config['type']) {
                case 'image_upload':
                    $fileFieldRules = ['nullable', 'mimes:jpeg,jpg,png,webp,svg'];

                    if (isset($config['max_size'])) {
                        $maxSize = $config['max_size'];
                        if (is_string($maxSize) && strtoupper(substr($maxSize, -2)) === 'MB') {
                            $sizeInMB = (int) substr($maxSize, 0, -2);
                            $fileFieldRules[] = "max:" . ($sizeInMB * 1024);
                        }
                    } else {
                        $fileFieldRules[] = 'max:2048';
                    }

                    $rules["upload_{$field}"] = implode('|', $fileFieldRules);
                    $rules["delete_{$field}"] = 'nullable|in:0,1';

                    $fieldRules[] = 'string';
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'video_upload':
                    $fileFieldRules = ['nullable', 'file', 'mimes:mp4,mov,avi,wmv,flv,webm,mkv'];

                    if (isset($config['max_size'])) {
                        $maxSize = $config['max_size'];
                        if (is_string($maxSize) && strtoupper(substr($maxSize, -2)) === 'MB') {
                            $sizeInMB = (int) substr($maxSize, 0, -2);
                            $fileFieldRules[] = "max:" . ($sizeInMB * 1024);
                        }
                    } else {
                        $fileFieldRules[] = 'max:51200'; // 50MB default for videos
                    }

                    $rules["upload_{$field}"] = implode('|', $fileFieldRules);
                    $rules["delete_{$field}"] = 'nullable|in:0,1';

                    $fieldRules[] = 'string';
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'file_upload':
                    $fileFieldRules = ['nullable', 'file'];

                    if (isset($config['accept'])) {
                        $mimes = str_replace(['.', '*'], ['', ''], $config['accept']);
                        $mimes = str_replace('/', ',', $mimes);
                        if ($mimes !== '*') {
                            $fileFieldRules[] = "mimes:{$mimes}";
                        }
                    }

                    if (isset($config['max_size'])) {
                        $maxSize = $config['max_size'];
                        if (is_string($maxSize) && strtoupper(substr($maxSize, -2)) === 'MB') {
                            $sizeInMB = (int) substr($maxSize, 0, -2);
                            $fileFieldRules[] = "max:" . ($sizeInMB * 1024);
                        }
                    } else {
                        $fileFieldRules[] = 'max:102400'; // 100MB default
                    }

                    $rules["upload_{$field}"] = implode('|', $fileFieldRules);
                    $rules["delete_{$field}"] = 'nullable|in:0,1';

                    $fieldRules[] = 'string';
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'url':
                    $fieldRules[] = 'url';
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'email':
                    $fieldRules[] = 'email';
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'select':
                    $fieldRules[] = 'string';
                    if (isset($config['options']) && is_array($config['options'])) {
                        $allowedValues = array_keys($config['options']);
                        $fieldRules[] = 'in:' . implode(',', $allowedValues);
                    }
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    if (isset($config['min'])) {
                        $fieldRules[] = "min:{$config['min']}";
                    }
                    if (isset($config['max'])) {
                        $fieldRules[] = "max:{$config['max']}";
                    }
                    $rules["text.{$field}"] = implode('|', $fieldRules);
                    break;

                case 'repeater':
                    $fieldRules[] = 'array';
                    if (isset($config['min_items'])) {
                        $fieldRules[] = "min:{$config['min_items']}";
                    }
                    if (isset($config['max_items'])) {
                        $fieldRules[] = "max:{$config['max_items']}";
                    }
                    $rules["text.{$field}"] = implode('|', $fieldRules);

                    if (isset($config['fields'])) {
                        $this->addRepeaterFieldRules($rules, $field, $config['fields']);
                    }
                    break;

                case 'group':
                    $fieldRules[] = 'array';
                    $rules["text.{$field}"] = implode('|', $fieldRules);

                    if (isset($config['fields'])) {
                        $this->addGroupFieldRules($rules, $field, $config['fields']);
                    }
                    break;

                default:
                    $fieldRules[] = 'string';

                    if (isset($config['max_length'])) {
                        $fieldRules[] = "max:{$config['max_length']}";
                    }

                    $rules["text.{$field}"] = implode('|', $fieldRules);
            }
        }

        return $rules;
    }

    private function addRepeaterFieldRules(array &$rules, string $parentField, array $fields): void
    {
        foreach ($fields as $subField => $subConfig) {
            $subFieldRules = [];
            if ($subConfig['required'] ?? false) {
                $subFieldRules[] = 'required';
            } else {
                $subFieldRules[] = 'nullable';
            }

            switch ($subConfig['type']) {
                case 'image_upload':
                    $subFileFieldRules = ['nullable', 'mimes:jpeg,jpg,png,webp,svg'];

                    if (isset($subConfig['max_size'])) {
                        $maxSize = $subConfig['max_size'];
                        if (is_string($maxSize) && strtoupper(substr($maxSize, -2)) === 'MB') {
                            $sizeInMB = (int) substr($maxSize, 0, -2);
                            $subFileFieldRules[] = "max:" . ($sizeInMB * 1024);
                        }
                    } else {
                        $subFileFieldRules[] = 'max:2048';
                    }

                    $rules["upload_{$parentField}_*_{$subField}"] = implode('|', $subFileFieldRules);
                    $subFieldRules = ['nullable', 'string'];
                    $rules["text.{$parentField}.*.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'repeater':
                    // Para repeaters anidados, establecer como array
                    $nestedFieldRules = ['nullable', 'array'];
                    if (isset($subConfig['min_items'])) {
                        $nestedFieldRules[] = "min:{$subConfig['min_items']}";
                    }
                    if (isset($subConfig['max_items'])) {
                        $nestedFieldRules[] = "max:{$subConfig['max_items']}";
                    }
                    $rules["text.{$parentField}.*.{$subField}"] = implode('|', $nestedFieldRules);

                    // Procesar campos del repeater anidado (solo 1 nivel)
                    if (isset($subConfig['fields'])) {
                        foreach ($subConfig['fields'] as $nestedField => $nestedConfig) {
                            $nestedFieldRules = ['nullable'];

                            switch ($nestedConfig['type']) {
                                case 'image_upload':
                                    $fileFieldRules = ['nullable', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'];
                                    $rules["upload_{$parentField}_*_{$subField}_*_{$nestedField}"] = implode('|', $fileFieldRules);
                                    $nestedFieldRules[] = 'string';
                                    break;
                                case 'url':
                                    $nestedFieldRules[] = 'url';
                                    break;
                                case 'email':
                                    $nestedFieldRules[] = 'email';
                                    break;
                                default:
                                    $nestedFieldRules[] = 'string';
                            }

                            $rules["text.{$parentField}.*.{$subField}.*.{$nestedField}"] = implode('|', $nestedFieldRules);
                        }
                    }
                    break;

                case 'url':
                    $subFieldRules[] = 'url';
                    $rules["text.{$parentField}.*.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'email':
                    $subFieldRules[] = 'email';
                    $rules["text.{$parentField}.*.{$subField}"] = implode('|', $subFieldRules);
                    break;

                default:
                    $subFieldRules[] = 'string';
                    $rules["text.{$parentField}.*.{$subField}"] = implode('|', $subFieldRules);
            }
        }
    }

    private function addGroupFieldRules(array &$rules, string $parentField, array $fields): void
    {
        foreach ($fields as $subField => $subConfig) {
            $subFieldRules = [];
            if ($subConfig['required'] ?? false) {
                $subFieldRules[] = 'required';
            } else {
                $subFieldRules[] = 'nullable';
            }

            switch ($subConfig['type']) {
                case 'image_upload':
                    $subFileFieldRules = ['nullable', 'mimes:jpeg,jpg,png,webp,gif,webp,svg'];

                    if (isset($subConfig['max_size'])) {
                        $maxSize = $subConfig['max_size'];
                        if (is_string($maxSize) && strtoupper(substr($maxSize, -2)) === 'MB') {
                            $sizeInMB = (int) substr($maxSize, 0, -2);
                            $subFileFieldRules[] = "max:" . ($sizeInMB * 1024);
                        }
                    } else {
                        $subFileFieldRules[] = 'max:2048';
                    }

                    $rules["upload_{$parentField}_{$subField}"] = implode('|', $subFileFieldRules);
                    $subFieldRules = ['nullable', 'string'];
                    $rules["text.{$parentField}.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'url':
                    $subFieldRules[] = 'url';
                    $rules["text.{$parentField}.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'email':
                    $subFieldRules[] = 'email';
                    $rules["text.{$parentField}.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'repeater':
                    $subFieldRules[] = 'array';
                    if (isset($subConfig['min_items'])) {
                        $subFieldRules[] = "min:{$subConfig['min_items']}";
                    }
                    if (isset($subConfig['max_items'])) {
                        $subFieldRules[] = "max:{$subConfig['max_items']}";
                    }
                    $rules["text.{$parentField}.{$subField}"] = implode('|', $subFieldRules);

                    // Add validation for repeater sub-fields
                    if (isset($subConfig['fields'])) {
                        $this->addGroupRepeaterFieldRules($rules, $parentField, $subField, $subConfig['fields']);
                    }
                    break;

                default:
                    $subFieldRules[] = 'string';
                    $rules["text.{$parentField}.{$subField}"] = implode('|', $subFieldRules);
            }
        }
    }

    private function addGroupRepeaterFieldRules(array &$rules, string $parentField, string $repeaterField, array $fields): void
    {
        foreach ($fields as $subField => $subConfig) {
            $subFieldRules = [];
            if ($subConfig['required'] ?? false) {
                $subFieldRules[] = 'required';
            } else {
                $subFieldRules[] = 'nullable';
            }

            switch ($subConfig['type']) {
                case 'url':
                    $subFieldRules[] = 'url';
                    $rules["text.{$parentField}.{$repeaterField}.*.{$subField}"] = implode('|', $subFieldRules);
                    break;

                case 'email':
                    $subFieldRules[] = 'email';
                    $rules["text.{$parentField}.{$repeaterField}.*.{$subField}"] = implode('|', $subFieldRules);
                    break;

                default:
                    $subFieldRules[] = 'string';
                    $rules["text.{$parentField}.{$repeaterField}.*.{$subField}"] = implode('|', $subFieldRules);
            }
        }
    }

    private function cleanRepeaterData(array $textData, array $layoutConfig): array
    {
        // Eliminar campos que no estén definidos en el layout
        $allowedFields = array_keys($layoutConfig['fields'] ?? []);

        foreach ($textData as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $allowedFields)) {
                unset($textData[$fieldName]);
            }
        }

        return $textData;
    }

    private function handleFileUpload($file, string $fileType): string
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'blocks/' . $filename;

        Storage::disk('public')->put($path, file_get_contents($file));

        // Only apply image optimization for image uploads
        if ($fileType === 'image_upload' && class_exists('Intervention\Image\Facades\Image')) {
            $image = Image::make($file);
            if ($image->width() > 800 || $image->height() > 600) {
                $image->fit(800, 600, function ($constraint) {
                    $constraint->upsize();
                });
                Storage::disk('public')->put($path, $image->encode());
            }
        }

        // Devolver el path relativo para compatibilidad con asset()
        return 'storage/' . $path;
    }

    // Keep the old method for backward compatibility
    private function handleImageUpload($file): string
    {
        return $this->handleFileUpload($file, 'image_upload');
    }

    private function processTextFields(Request $request, array $layoutConfig): array
    {
        $textData = [];
        $fields = $layoutConfig['fields'] ?? [];

        \Log::info('Processing Text Fields - Input Data:', $request->input('text', []));

        foreach ($fields as $fieldName => $fieldConfig) {
            if ($request->has("text.{$fieldName}")) {
                $value = $request->input("text.{$fieldName}");

                if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'repeater' && is_array($value)) {
                    $textData[$fieldName] = $this->processRepeaterData($value, $fieldConfig);
                } elseif (isset($fieldConfig['type']) && $fieldConfig['type'] === 'editor') {
                    // Sanitizar contenido HTML para prevenir XSS usando sanitización inteligente
                    $unescapedValue = $this->unescapeEditorContent($value);
                    $textData[$fieldName] = $this->htmlSanitizer->smartSanitize($unescapedValue, 'admin');
                } elseif (isset($fieldConfig['type']) && $fieldConfig['type'] === 'group' && is_array($value)) {
                    // Process group fields which might contain editors
                    $textData[$fieldName] = $this->processGroupData($value, $fieldConfig);
                } else {
                    $textData[$fieldName] = $value;
                }
            }
        }

        return $textData;
    }

    private function processRepeaterData(array $items, array $fieldConfig): array
    {
        $processedItems = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                \Log::warning("Item $index is not an array:", ['item' => $item, 'type' => gettype($item)]);
                continue;
            }

            $processedItem = [];
            $hasNonEmptyFields = false;

            foreach ($item as $key => $value) {
                $subFieldConfig = $fieldConfig['fields'][$key] ?? null;

                if ($subFieldConfig && isset($subFieldConfig['type']) && $subFieldConfig['type'] === 'editor') {
                    $unescapedValue = $this->unescapeEditorContent($value);
                    $processedItem[$key] = $this->htmlSanitizer->smartSanitize($unescapedValue, 'admin');
                } else {
                    $processedItem[$key] = $value;
                }

                // Verificar si el campo tiene contenido significativo
                if ($this->hasSignificantContent($value, $subFieldConfig ?? [])) {
                    $hasNonEmptyFields = true;
                }
            }

            // Para repeaters anidados, siempre incluir el item (las imágenes se procesarán después)
            // Solo filtrar si realmente todos los campos están completamente vacíos
            $isCompletelyEmpty = empty(array_filter($item, function($val) {
                return !empty($val) || $val === '0' || $val === 0;
            }));

            if (!$isCompletelyEmpty) {
                $processedItems[] = $processedItem;
            }
        }

        return array_values($processedItems);
    }

    private function hasSignificantContent($value, array $fieldConfig): bool
    {
        if (empty($fieldConfig)) {
            return !empty($value);
        }

        // Para campos de tipo repeater anidado, verificar si tiene items
        if (($fieldConfig['type'] ?? '') === 'repeater') {
            return is_array($value) && count($value) > 0;
        }

        // Para otros tipos de campo, verificar si no está vacío
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return !empty($value);
    }

    private function validateRepeaterImages(Request $request, array $layoutConfig): void
    {
        // Simplificada: no validamos imágenes required por ahora para evitar complejidad
        // Se puede agregar después si es necesario
    }


    private function preserveExistingImages(Block $block, array $layoutConfig, array $textData): array
    {
        $existingData = $block->getDataAsArray();

        foreach ($layoutConfig['fields'] ?? [] as $fieldName => $fieldConfig) {
            if ($fieldConfig['type'] === 'repeater' && isset($fieldConfig['fields'])) {
                $existingRepeaterData = $existingData[$fieldName] ?? [];
                $newRepeaterData = $textData[$fieldName] ?? [];

                foreach ($newRepeaterData as $index => $newItem) {
                    // Asegurarse de que $newItem es un array
                    if (!is_array($newItem)) {
                        continue;
                    }

                    $existingItem = $existingRepeaterData[$index] ?? [];
                    // Asegurarse de que $existingItem también es un array
                    if (!is_array($existingItem)) {
                        $existingItem = [];
                    }

                    foreach ($fieldConfig['fields'] as $subFieldName => $subFieldConfig) {
                        if (isset($subFieldConfig['type']) && $subFieldConfig['type'] === 'image_upload') {
                            // Preservar imagen simple del repeater
                            if (empty($newItem[$subFieldName]) && !empty($existingItem[$subFieldName])) {
                                $textData[$fieldName][$index][$subFieldName] = $existingItem[$subFieldName];
                            }
                        }
                        elseif (isset($subFieldConfig['type']) && $subFieldConfig['type'] === 'repeater' && isset($subFieldConfig['fields'])) {
                            // Preservar imágenes de repeater anidado
                            $existingNestedData = $existingItem[$subFieldName] ?? [];
                            $newNestedData = $newItem[$subFieldName] ?? [];

                            // Asegurarse de que ambos son arrays
                            if (!is_array($existingNestedData)) {
                                $existingNestedData = [];
                            }
                            if (!is_array($newNestedData)) {
                                $newNestedData = [];
                            }

                            foreach ($newNestedData as $nestedIndex => $newNestedItem) {
                                // Asegurarse de que $newNestedItem es un array
                                if (!is_array($newNestedItem)) {
                                    continue;
                                }

                                $existingNestedItem = $existingNestedData[$nestedIndex] ?? [];
                                // Asegurarse de que $existingNestedItem también es un array
                                if (!is_array($existingNestedItem)) {
                                    $existingNestedItem = [];
                                }

                                foreach ($subFieldConfig['fields'] as $nestedSubFieldName => $nestedSubFieldConfig) {
                                    if (isset($nestedSubFieldConfig['type']) && $nestedSubFieldConfig['type'] === 'image_upload') {
                                        if (empty($newNestedItem[$nestedSubFieldName]) && !empty($existingNestedItem[$nestedSubFieldName])) {
                                            $textData[$fieldName][$index][$subFieldName][$nestedIndex][$nestedSubFieldName] = $existingNestedItem[$nestedSubFieldName];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $textData;
    }

    private function unescapeEditorContent($content)
    {
        if (!is_string($content)) {
            return $content;
        }

        // Unescape HTML entities that were escaped for safe transport
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    private function processGroupData(array $groupData, array $groupConfig): array
    {
        $processedGroup = [];
        $fields = $groupConfig['fields'] ?? [];

        foreach ($groupData as $fieldName => $fieldValue) {
            $fieldConfig = $fields[$fieldName] ?? null;

            if ($fieldConfig && isset($fieldConfig['type']) && $fieldConfig['type'] === 'editor') {
                $unescapedValue = $this->unescapeEditorContent($fieldValue);
                $processedGroup[$fieldName] = $this->htmlSanitizer->smartSanitize($unescapedValue, 'admin');
            } elseif ($fieldConfig && isset($fieldConfig['type']) && $fieldConfig['type'] === 'repeater' && is_array($fieldValue)) {
                $processedGroup[$fieldName] = $this->processRepeaterData($fieldValue, $fieldConfig);
            } else {
                $processedGroup[$fieldName] = $fieldValue;
            }
        }

        return $processedGroup;
    }

    /**
     * Preview block template with provided data for visual editor
     */
    public function preview(Request $request)
    {
        $blockType = $request->input('block_type');
        $data = $request->input('data', []);

        if (!$blockType || !isset(config('blocks.layouts')[$blockType])) {
            return response('Invalid block type', 400);
        }

        // Create a mock block object with the provided data
        $mockBlock = new class {
            public $block_type;
            public $data;
            public $data_preview;
            public $styles;
            public $image_path;
            public $image;

            public function getData($key = null) {
                if ($key === null) {
                    return $this->data;
                }
                return $this->data->{$key} ?? null;
            }

            public function getDataAsArray() {
                return json_decode(json_encode($this->data), true);
            }

            public function getStyleValue(string $key, $default = null) {
                if (!$this->styles) {
                    return $default;
                }

                if (is_object($this->styles)) {
                    return $this->styles->{$key} ?? $default;
                }

                if (is_array($this->styles)) {
                    return $this->styles[$key] ?? $default;
                }

                return $default;
            }

            public function getStylesAsArray() {
                if (!$this->styles) {
                    return [];
                }

                if (is_object($this->styles)) {
                    return json_decode(json_encode($this->styles), true);
                }

                if (is_array($this->styles)) {
                    return $this->styles;
                }

                return [];
            }

            public function getComputedStyles() {
                $styles = $this->getStylesAsArray();
                $css = [];

                // Padding
                if (!empty($styles['padding_top'])) {
                    $css[] = "padding-top: {$styles['padding_top']}";
                }
                if (!empty($styles['padding_right'])) {
                    $css[] = "padding-right: {$styles['padding_right']}";
                }
                if (!empty($styles['padding_bottom'])) {
                    $css[] = "padding-bottom: {$styles['padding_bottom']}";
                }
                if (!empty($styles['padding_left'])) {
                    $css[] = "padding-left: {$styles['padding_left']}";
                }

                // Margin
                if (!empty($styles['margin_top'])) {
                    $css[] = "margin-top: {$styles['margin_top']}";
                }
                if (!empty($styles['margin_right'])) {
                    $css[] = "margin-right: {$styles['margin_right']}";
                }
                if (!empty($styles['margin_bottom'])) {
                    $css[] = "margin-bottom: {$styles['margin_bottom']}";
                }
                if (!empty($styles['margin_left'])) {
                    $css[] = "margin-left: {$styles['margin_left']}";
                }

                // Background
                if (!empty($styles['background_color'])) {
                    $css[] = "background-color: {$styles['background_color']}";
                }
                if (!empty($styles['background_image'])) {
                    $css[] = "background-image: url('{$styles['background_image']}')";
                }
                if (!empty($styles['background_size'])) {
                    $css[] = "background-size: {$styles['background_size']}";
                }
                if (!empty($styles['background_position'])) {
                    $css[] = "background-position: {$styles['background_position']}";
                }
                if (!empty($styles['background_repeat'])) {
                    $css[] = "background-repeat: {$styles['background_repeat']}";
                }

                // Text Color
                if (!empty($styles['color'])) {
                    $css[] = "color: {$styles['color']}";
                }

                // Border
                if (!empty($styles['border_width'])) {
                    $css[] = "border-width: {$styles['border_width']}";
                }
                if (!empty($styles['border_style'])) {
                    $css[] = "border-style: {$styles['border_style']}";
                }
                if (!empty($styles['border_color'])) {
                    $css[] = "border-color: {$styles['border_color']}";
                }
                if (!empty($styles['border_radius'])) {
                    $css[] = "border-radius: {$styles['border_radius']}";
                }

                // Custom CSS
                if (!empty($styles['custom_css'])) {
                    $css[] = $styles['custom_css'];
                }

                return !empty($css) ? implode('; ', $css) : '';
            }
        };

        $mockBlock->block_type = $blockType;
        // Convert array to object so the templates can access properties with ->
        $mockBlock->data = json_decode(json_encode($data));
        $mockBlock->styles = $data['styles'] ?? [];
        $mockBlock->image_path = $data['image_path'] ?? null;
        $mockBlock->image = $data['image_path'] ?? null;

        try {
            // Render the block template
            $html = view('blocks.' . $blockType, ['block' => $mockBlock])->render();

            // Apply styles if there are any
            $blockStyles = isset($data['styles']) ? $this->computeStylesFromArray($data['styles']) : '';
            if ($blockStyles) {
                $html = '<div style="' . htmlspecialchars($blockStyles, ENT_QUOTES, 'UTF-8') . '" class="block-wrapper block-' . $blockType . '">' . $html . '</div>';
            }

            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            // If template doesn't exist or has errors, return a placeholder
            return response('<div class="p-8 text-center bg-gray-100 border border-gray-300 rounded">
                <h3 class="text-lg font-semibold text-gray-700">Vista previa no disponible</h3>
                <p class="text-gray-600 mt-2">Error al cargar la plantilla: ' . $e->getMessage() . '</p>
            </div>', 200, ['Content-Type' => 'text/html']);
        }
    }

    /**
     * Iframe preview for visual editor with public layout
     */
    public function iframePreview(Request $request, $blockId = null)
    {
        if ($blockId) {
            // Entrando en rama de bloque existente
            // Preview existing block, potentially with updated data from form
            $block = Block::findOrFail($blockId);

            // Bloque cargado correctamente

            // Si hay datos de preview, usarlos temporalmente para mostrar (sin guardar)
            if ($block->data_preview && !empty($block->data_preview)) {
                $originalData = $block->data;
                $block->data = $block->data_preview;
            }

            // Ver si hay datos del formulario
            $formData = $request->except(['_token', '_method', 'blockId']);
            if (!empty($formData)) {
                // Hay datos del formulario, procesando
                // If form data is provided, override the block data for preview
                // Get the existing data as a starting point
                $existingData = $block->getDataAsArray();
                $layoutConfig = config("blocks.layouts.{$block->block_type}", []);

                // Process form data using the same methods as update()
                $textData = [];
                if ($request->has('text')) {
                    foreach ($request->input('text') as $fieldName => $value) {
                        $fieldConfig = $layoutConfig['fields'][$fieldName] ?? null;
                        if (!$fieldConfig) continue;

                        if ($fieldConfig['type'] === 'repeater' && is_array($value)) {
                            $textData[$fieldName] = $this->processSimpleRepeater($value, $fieldConfig, $existingData[$fieldName] ?? []);
                        } else {
                            $textData[$fieldName] = $value;
                        }
                    }
                }

                // Process files for preview using the same methods as update()
                $textData = $this->processSimpleRepeaterFiles($request, $textData, $block->block_type);

                // Actualizar bloque original temporalmente para preview (sin guardar)
                $block->data = json_decode(json_encode($textData));
                $block->styles = (object) ($formData['styles'] ?? (array) $block->styles);
                if (isset($formData['image_path'])) {
                    $block->image_path = $formData['image_path'];
                    $block->image = $formData['image_path'];
                }
            }
        } else {
            // Entrando en rama de bloque nuevo
            // Create block for new blocks (use standard Block model)
            $block = new Block();

            $formData = $request->except(['_token', '_method']);
            $blockType = $request->input('block_type', 'hero');

            // Filter out system fields and create preview data
            $filteredData = array_filter($formData, function ($key) {
                return !in_array($key, ['page_id', 'block_type', 'is_active', 'sort_order']);
            }, ARRAY_FILTER_USE_KEY);

            $block->id = null;
            $block->block_type = $blockType;
            $block->data = json_decode(json_encode($filteredData));
            $block->styles = $filteredData['styles'] ?? [];
            $block->image_path = $filteredData['image_path'] ?? null;
            $block->image = $filteredData['image_path'] ?? null;
            $block->is_active = true;
        }

        try {
            // Debug: Ver el bloque final justo antes de renderizar
            // Bloque final antes de pasar a la vista

            // Render the block with the public layout
            return view('admin.blocks.iframe-preview', compact('block'));
        } catch (\Exception $e) {
            // Error en iframePreview
            return view('admin.blocks.iframe-preview-error', ['error' => $e->getMessage()]);
        }
    }

    private function computeStylesFromArray(array $styles): string
    {
        $css = [];

        // Padding
        if (!empty($styles['padding_top'])) {
            $css[] = "padding-top: {$styles['padding_top']}";
        }
        if (!empty($styles['padding_right'])) {
            $css[] = "padding-right: {$styles['padding_right']}";
        }
        if (!empty($styles['padding_bottom'])) {
            $css[] = "padding-bottom: {$styles['padding_bottom']}";
        }
        if (!empty($styles['padding_left'])) {
            $css[] = "padding-left: {$styles['padding_left']}";
        }

        // Margin
        if (!empty($styles['margin_top'])) {
            $css[] = "margin-top: {$styles['margin_top']}";
        }
        if (!empty($styles['margin_right'])) {
            $css[] = "margin-right: {$styles['margin_right']}";
        }
        if (!empty($styles['margin_bottom'])) {
            $css[] = "margin-bottom: {$styles['margin_bottom']}";
        }
        if (!empty($styles['margin_left'])) {
            $css[] = "margin-left: {$styles['margin_left']}";
        }

        // Background
        if (!empty($styles['background_color'])) {
            $css[] = "background-color: {$styles['background_color']}";
        }
        if (!empty($styles['background_image'])) {
            $css[] = "background-image: url('{$styles['background_image']}')";
        }
        if (!empty($styles['background_size'])) {
            $css[] = "background-size: {$styles['background_size']}";
        }
        if (!empty($styles['background_position'])) {
            $css[] = "background-position: {$styles['background_position']}";
        }
        if (!empty($styles['background_repeat'])) {
            $css[] = "background-repeat: {$styles['background_repeat']}";
        }

        // Text Color
        if (!empty($styles['color'])) {
            $css[] = "color: {$styles['color']}";
        }

        // Border
        if (!empty($styles['border_width'])) {
            $css[] = "border-width: {$styles['border_width']}";
        }
        if (!empty($styles['border_style'])) {
            $css[] = "border-style: {$styles['border_style']}";
        }
        if (!empty($styles['border_color'])) {
            $css[] = "border-color: {$styles['border_color']}";
        }
        if (!empty($styles['border_radius'])) {
            $css[] = "border-radius: {$styles['border_radius']}";
        }

        // Custom CSS
        if (!empty($styles['custom_css'])) {
            $css[] = $styles['custom_css'];
        }

        return !empty($css) ? implode('; ', $css) : '';
    }


    public function showPreview(Request $request, $previewId = null)
    {
        $sessionId = session()->getId();

        if ($previewId) {
            // Mostrar previsualización específica
            $preview = BlockPreview::where('id', $previewId)
                ->where('session_id', $sessionId)
                ->first();

            if (!$preview) {
                return response($this->wrapInHtml('<div class="p-8 text-center bg-gray-100 border border-gray-300 rounded">
                <h3 class="text-lg font-semibold text-gray-700">Previsualización no encontrada</h3>
                <p class="text-gray-600 mt-2">La previsualización ha expirado o no existe.</p>
            </div>'), 404, ['Content-Type' => 'text/html']);
            }
        } else {
            // Buscar la previsualización más reciente de esta sesión
            $blockId = $request->input('block_id');
            $preview = BlockPreview::where('session_id', $sessionId)
                ->where('block_id', $blockId)
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$preview) {
                return response($this->wrapInHtml('<div class="p-8 text-center bg-gray-100 border border-gray-300 rounded">
                <h3 class="text-lg font-semibold text-gray-700">No hay previsualización disponible</h3>
                <p class="text-gray-600 mt-2">Haga clic en "Previsualizar" para ver el resultado.</p>
            </div>'), 200, ['Content-Type' => 'text/html']);
            }
        }

        try {
            // Renderizar el bloque usando la plantilla correspondiente
            $html = $this->renderBlockPreview($preview);
            return response($this->wrapInHtml($html), 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return response($this->wrapInHtml('<div class="p-8 text-center bg-gray-100 border border-red-300 rounded">
            <h3 class="text-lg font-semibold text-red-700">Error en la previsualización</h3>
            <p class="text-gray-600 mt-2">' . $e->getMessage() . '</p>
        </div>'), 500, ['Content-Type' => 'text/html']);
        }
    }

    private function wrapInHtml($content)
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualización - Sitio Web</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- Site CSS -->
    <link href="' . asset('assets/css/style.css') . '">

    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .preview-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            min-height: calc(100vh - 40px);
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="preview-container">
    ' . $content . '
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Site JS -->
    <script src="' . asset('assets/js/main.js') . '"></script>
  
    <!-- Script to replace image placeholders with data URLs from sessionStorage -->
    <script>
    console.log("=== IFRAME SCRIPT LOADED ===");

    function replaceImagePlaceholders() {
        console.log("=== REPLACING IMAGE PLACEHOLDERS ===");
        try {
            // Try to get sessionStorage from parent or current window
            const previewImages = window.parent ?
                window.parent.sessionStorage.getItem("previewImages") :
                sessionStorage.getItem("previewImages");

            console.log("Raw previewImages from sessionStorage:", previewImages);

            if (previewImages) {
                const imageData = JSON.parse(previewImages);
                console.log("Parsed imageData:", imageData);
                console.log("Available fields:", Object.keys(imageData));

                // Find all images with __IMAGE_ in their src
                const imagesWithPlaceholders = document.querySelectorAll(\'img[src*="__IMAGE_"]\');
                console.log("Found images with placeholders:", imagesWithPlaceholders.length);

                imagesWithPlaceholders.forEach((img, index) => {
                    console.log("Image " + index + " src:", img.src);
                });

                // Also check all img elements
                const allImages = document.querySelectorAll(\'img\');
                console.log("Total images found:", allImages.length);
                allImages.forEach((img, index) => {
                    console.log("Image " + index + " src:", img.src);
                });

                // Replace image placeholders in the content
                for (const [fieldName, dataURL] of Object.entries(imageData)) {
                    const placeholder = "__IMAGE_" + fieldName + "__";
                    console.log("Looking for placeholder:", placeholder);

                    // Replace in attributes like src, href, etc.
                    let replacedAttributes = 0;
                    document.querySelectorAll("*").forEach(el => {
                        Array.from(el.attributes).forEach(attr => {
                            if (attr.value === placeholder) {
                                // Replace exact match only
                                attr.value = dataURL;
                                console.log("✓ Replaced attribute:", el.tagName, attr.name, "exact match ->", dataURL.substring(0, 50) + "...");
                                replacedAttributes++;
                            }
                        });
                    });
                    console.log("Replaced", replacedAttributes, "attributes for field", fieldName);
                }
            } else {
                console.log("No previewImages found in sessionStorage");
            }
        } catch (error) {
            console.error("Error processing preview images:", error);
        }
    }

    // Execute replacement on multiple events to ensure timing is right
    document.addEventListener("DOMContentLoaded", replaceImagePlaceholders);
    window.addEventListener("load", function() {
        console.log("=== IFRAME LOADED ===");
        replaceImagePlaceholders();
        setTimeout(replaceImagePlaceholders, 100);
        setTimeout(replaceImagePlaceholders, 500);
    });
    </script>
</body>
</html>';
    }

    /**
     * Renderizar un BlockPreview usando su plantilla
     */
    private function renderBlockPreview(BlockPreview $preview): string
    {
        $layout = $preview->block_type;
        $viewPath = "blocks.{$layout}";

        if (!view()->exists($viewPath)) {
            return "<!-- Block template '{$viewPath}' not found -->";
        }

        try {
            $blockStyles = $preview->getComputedStyles();
            $content = view($viewPath, [
                'block' => $preview,
                'text' => $preview->data,
                'data' => $preview->data,
            ])->render();

            // Wrap content with styles if there are any
            if ($blockStyles) {
                return '<div style="' . htmlspecialchars($blockStyles, ENT_QUOTES, 'UTF-8') . '" class="block-wrapper block-' . $preview->block_type . '">' . $content . '</div>';
            }

            return $content;
        } catch (\Exception $e) {
            throw new \Exception("Error rendering block: {$e->getMessage()}");
        }
    }

    /**
     * Guardar definitivamente un BlockPreview como Block
     */
    public function saveFromPreview(Request $request)
    {
        $sessionId = session()->getId();
        $previewId = $request->input('preview_id');

        $preview = BlockPreview::where('id', $previewId)
            ->where('session_id', $sessionId)
            ->first();

        if (!$preview) {
            return response()->json([
                'success' => false,
                'message' => 'Previsualización no encontrada'
            ], 404);
        }

        try {
            if ($preview->block_id) {
                // Actualizar bloque existente
                $block = Block::findOrFail($preview->block_id);
                $blockData = $preview->toBlock();

                // Preservar sort_order original si no se especifica uno nuevo
                if (!isset($blockData['sort_order']) || $blockData['sort_order'] === 0) {
                    $blockData['sort_order'] = $block->sort_order;
                }

                $block->update($blockData);
                $message = 'Bloque actualizado exitosamente.';
                $route = 'admin.blocks.edit';
                $routeParams = $block->id;
            } else {
                // Crear nuevo bloque
                $blockData = $preview->toBlock();

                // Asegurar sort_order si no existe o es 0
                if (!isset($blockData['sort_order']) || $blockData['sort_order'] === null || $blockData['sort_order'] === 0) {
                    $maxSortOrder = Block::where('page_id', $blockData['page_id'])->max('sort_order') ?? 0;
                    $blockData['sort_order'] = $maxSortOrder + 1;
                }

                $block = Block::create($blockData);
                $message = 'Bloque creado exitosamente.';
                $route = 'admin.blocks.edit';
                $routeParams = $block->id;
            }

            // Limpiar la previsualización
            $preview->delete();

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route($route, $routeParams)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function savePreview(Request $request)
    {
        $blockId = $request->input('block_id');

        if (!$blockId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere ID de bloque para previsualizar'
            ], 400);
        }

        $block = Block::findOrFail($blockId);

        $layoutConfig = config("blocks.layouts.{$request->block_type}", []);
        $rules = $this->getValidationRules($request->block_type, $layoutConfig, $block->id);

        $validated = $request->validate($rules);

        // Obtener los datos existentes del bloque
        $existingData = $block->getDataAsArray();

        // Procesar campos de texto (IGUAL que en update)
        $textData = [];

        if ($request->has('text')) {
            foreach ($request->input('text') as $fieldName => $value) {
                $fieldConfig = $layoutConfig['fields'][$fieldName] ?? null;

                if (!$fieldConfig) continue;

                if ($fieldConfig['type'] === 'repeater' && is_array($value)) {
                    // Procesar repeater de forma simple
                    $textData[$fieldName] = $this->processSimpleRepeater($value, $fieldConfig, $existingData[$fieldName] ?? []);
                } else {
                    // Otros tipos de campo
                    $textData[$fieldName] = $value;
                }
            }
        }

        // Procesar archivos de imagen para repeaters (método simple)
        $textData = $this->processSimpleRepeaterFiles($request, $textData, $request->block_type);

        // Procesar archivos de campos principales (IGUAL que en update)
        foreach ($layoutConfig['fields'] ?? [] as $fieldName => $fieldConfig) {
            if (in_array($fieldConfig['type'], ['image_upload', 'video_upload', 'file_upload'])) {
                if ($request->has("delete_{$fieldName}") && $request->input("delete_{$fieldName}") == '1') {
                    $oldFilePath = $block->getDataValue($fieldName);
                    if ($oldFilePath && Storage::disk('public')->exists(ltrim($oldFilePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($oldFilePath, '/'));
                    }
                    $textData[$fieldName] = '';
                } elseif ($request->hasFile("upload_{$fieldName}")) {
                    $oldFilePath = $block->getDataValue($fieldName);
                    if ($oldFilePath && Storage::disk('public')->exists(ltrim($oldFilePath, '/'))) {
                        Storage::disk('public')->delete(ltrim($oldFilePath, '/'));
                    }

                    $filePath = $this->handleFileUpload($request->file("upload_{$fieldName}"), $fieldConfig['type']);
                    $textData[$fieldName] = $filePath;
                } else {
                    // Mantener valor existente si no hay cambios
                    if (!isset($textData[$fieldName])) {
                        $existingFilePath = $block->getDataValue($fieldName);
                        if ($existingFilePath) {
                            $textData[$fieldName] = $existingFilePath;
                        }
                    }
                }
            }
        }

        // Procesar imagen principal (IGUAL que en update)
        if ($request->hasFile('image')) {
            if ($block->image_path) {
                Storage::disk('public')->delete($block->image_path);
            }
            $validated['image_path'] = $this->handleImageUpload($request->file('image'));
        }

        // GUARDAR EN DATA_PREVIEW EN VEZ DE DATA
        $validated['data_preview'] = $textData;

        // Preservar sort_order original si no se especifica uno nuevo
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $block->sort_order;
        }

        $block->update($validated);

        return response()->json([
            'success' => true,
            'preview_id' => $blockId,
            'message' => 'Previsualización guardada'
        ]);
    }
}