<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;

class ImportProductsFromFolders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import-from-folders
                            {--cleanup-first : Eliminar todos los productos existentes antes de importar}
                            {--path=storage/app/private/products : Ruta a la carpeta de productos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar productos desde estructura: Categoría > Marca > Producto';

    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        parent::__construct();
        $this->mediaService = $mediaService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->option('path');
        $cleanupFirst = $this->option('cleanup-first');

        // Siempre hacer cleanup al inicio
        $this->cleanup();

        $this->info('Iniciando importación de productos...');
        $this->info("Ruta: {$path}");

        // Obtener todas las carpetas de productos
        $folders = $this->getProductFolders($path);

        if (empty($folders)) {
            $this->warn('No se encontraron carpetas de productos.');
            return self::SUCCESS;
        }

        $this->info("Se encontraron " . count($folders) . " carpetas de productos.");

        $bar = $this->output->createProgressBar(count($folders));
        $bar->start();

        $importedCount = 0;
        $errorsCount = 0;

        foreach ($folders as $folder) {
            try {
                $this->importProductFromFolder($folder);
                $importedCount++;
            } catch (\Exception $e) {
                $this->newLine(2);
                $this->error("Error importando {$folder['product']}: " . $e->getMessage());
                $errorsCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Importación completada!");
        $this->info("📦 Productos importados: {$importedCount}");
        if ($errorsCount > 0) {
            $this->warn("⚠️  Errores: {$errorsCount}");
        }

        return self::SUCCESS;
    }

    /**
     * Eliminar todos los productos, categorías y marcas existentes
     */
    protected function cleanup(): void
    {
        $this->info('Limpiando datos existentes...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar media de productos
        $mediaProducts = Storage::disk('public')->directories('media/Product');
        foreach ($mediaProducts as $folder) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        Media::where('mediable_type', 'App\\Models\\Product')->delete();
        Product::truncate();
        Category::truncate();
        Brand::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('✅ Datos limpiados correctamente.');
        $this->newLine();
    }

    /**
     * Obtener lista de carpetas de productos con estructura: Categoría > Producto (con imágenes)
     * Retorna un array con información de la estructura
     */
    protected function getProductFolders(string $path): array
    {
        $fullPath = base_path($path);

        if (!is_dir($fullPath)) {
            $this->error("La ruta no existe: {$fullPath}");
            return [];
        }

        $products = [];

        // Leer categorías (primer nivel)
        $categories = scandir($fullPath);

        foreach ($categories as $category) {
            if ($category === '.' || $category === '..') {
                continue;
            }

            $categoryPath = $fullPath . '/' . $category;

            if (!is_dir($categoryPath)) {
                continue;
            }

            // Leer carpetas de productos (segundo nivel)
            $productFolders = scandir($categoryPath);

            foreach ($productFolders as $productFolder) {
                if ($productFolder === '.' || $productFolder === '..') {
                    continue;
                }

                $productPath = $categoryPath . '/' . $productFolder;

                if (is_dir($productPath)) {
                    $products[] = [
                        'category' => $category,
                        'product_folder' => $productFolder,
                        'full_path' => $productPath,
                    ];
                }
            }
        }

        return $products;
    }

    /**
     * Importar productos desde una carpeta con imágenes
     * Agrupa imágenes por código de producto (patrón: [1-2]-[CODIGO]-C[COLOR].jpg)
     */
    protected function importProductFromFolder(array $folderData): void
    {
        $categoryName = $folderData['category'];
        $productFolder = $folderData['product_folder'];
        $folderPath = $folderData['full_path'];

        // Obtener todas las imágenes de la carpeta
        $images = $this->getImagesFromFolder($folderPath);

        if (empty($images)) {
            $this->warn("No se encontraron imágenes en {$productFolder}");
            return;
        }

        // Agrupar imágenes por código de producto
        $groupedImages = $this->groupImagesByProductCode($images);

        if (empty($groupedImages)) {
            $this->warn("No se pudo extraer códigos de producto de {$productFolder}");
            return;
        }

        // Extraer marca del nombre de la carpeta
        $brandName = $this->extractBrandFromFolderName($productFolder);

        // Obtener o crear categoría y marca
        $category = $this->getOrCreateCategory($categoryName);
        $brand = $this->getOrCreateBrand($brandName);

        // Crear un producto por cada código encontrado
        foreach ($groupedImages as $productCode => $imagesData) {
            $this->createProductFromImages($productCode, $imagesData, $brand, $category, $productFolder);
        }
    }

    /**
     * Extraer código de producto del nombre de la carpeta
     */
    protected function extractProductCode(string $folderName): string
    {
        // Intentar extraer código que pueda tener el formato "XXX-1234" o similar
        if (preg_match('/[A-Z]+[-\s]*\d+/', $folderName, $matches)) {
            return str_replace(' ', '', $matches[0]);
        }

        // Extraer las primeras letras y números significativos
        if (preg_match('/^[A-Z]+/', $folderName, $matches)) {
            $prefix = $matches[0];
            if (preg_match('/\d+/', $folderName, $numberMatches)) {
                return $prefix . '-' . $numberMatches[0];
            }
            return $prefix;
        }

        // Si no encuentra, usar el nombre de la carpeta limpio
        return Str::upper(Str::slug($folderName, ''));
    }

    /**
     * Obtener o crear categoría desde el nombre de la carpeta
     */
    protected function getOrCreateCategory(string $categoryName): Category
    {
        $slug = Str::slug($categoryName);

        return Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $categoryName,
                'excerpt' => "Colección de {$categoryName}",
                'description' => "Descubre nuestra selección de {$categoryName} de las mejores marcas.",
                'sort_order' => 0,
                'is_active' => true,
            ]
        );
    }

    /**
     * Obtener o crear marca desde el nombre de la carpeta
     */
    protected function getOrCreateBrand(string $brandName): Brand
    {
        $slug = Str::slug($brandName);

        // Primero buscar por slug
        $brand = Brand::where('slug', $slug)->first();

        if ($brand) {
            return $brand;
        }

        // Generar código único
        $code = $this->generateUniqueBrandCode($brandName);

        return Brand::create([
            'slug' => $slug,
            'code' => $code,
            'name' => $brandName,
            'description' => "{$brandName} - Eyewear Collection",
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Generar código único para la marca
     */
    protected function generateUniqueBrandCode(string $brandName): string
    {
        // Tomar las primeras 3 letras y limpiar
        $baseCode = strtoupper(preg_replace('/[^A-Z]/i', '', substr($brandName, 0, 3)));

        // Si el código base está vacío o muy corto, usar más caracteres
        if (strlen($baseCode) < 2) {
            $baseCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($brandName, 0, 5)));
        }

        // Si aún está vacío, generar un código aleatorio
        if (empty($baseCode)) {
            $baseCode = strtoupper(substr(md5($brandName), 0, 3));
        }

        // Verificar si ya existe
        $code = $baseCode;
        $counter = 1;

        while (Brand::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Generar precio basado en la marca
     */
    protected function generatePriceForBrand(string $brandName): int
    {
        // Marcas premium con precios más altos
        $premiumBrands = ['Ray-Ban', 'Oakley', 'Prada', 'Gucci', 'Dior', 'Chanel', 'Tom Ford'];

        if (in_array($brandName, $premiumBrands)) {
            return rand(450000, 950000);
        }

        return rand(350000, 650000);
    }

    /**
     * Obtener imágenes de una carpeta
     */
    protected function getImagesFromFolder(string $folderPath): array
    {
        $images = [];
        $files = scandir($folderPath);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $folderPath . '/' . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (is_file($filePath) && in_array($extension, $allowedExtensions)) {
                $images[] = $filePath;
            }
        }

        sort($images);

        return $images;
    }

    /**
     * Generar slug único basado en marca, código y nombre de carpeta
     */
    protected function generateUniqueSlug(string $brand, string $code, string $folderName): string
    {
        // Extraer prefijo único de la carpeta (ej: "01-" de "01- Cam-001 53 21-145 C1")
        $folderPrefix = '';
        if (preg_match('/^(\d+)-/', $folderName, $matches)) {
            $folderPrefix = $matches[1] . '-';
        }

        // Crear slug único
        $baseSlug = Str::slug("{$brand}-{$folderPrefix}{$code}");

        // Verificar si ya existe y agregar sufijo si es necesario
        $counter = 1;
        $slug = $baseSlug;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Procesar imagen para producto usando MediaService
     */
    protected function processImageForProduct(string $imagePath, Product $product, int $sortOrder, ?string $colorCode = null): void
    {
        try {
            // Crear UploadedFile desde la imagen existente
            $file = new File($imagePath);
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $imagePath,
                basename($imagePath),
                mime_content_type($imagePath),
                filesize($imagePath),
                0,
                true
            );

            // Usar MediaService para procesar la imagen
            $media = $this->mediaService->storeMedia($uploadedFile, $product, 'image', $sortOrder);

            // Actualizar el title con el código de color si existe
            if ($media && $colorCode) {
                $media->update(['title' => $colorCode]);
            }

        } catch (\Exception $e) {
            $this->warn("Error procesando imagen {$imagePath}: " . $e->getMessage());
        }
    }

    /**
     * Agrupar imágenes por código de producto
     * Soporta dos patrones:
     * 1. [1-2]-[CODIGO]-C[COLOR].jpg (ej: 1-8875-54-15-C1.jpg)
     * 2. [CODIGO]-[NUMERO]-C[COLOR].jpg (ej: 004 5 5 16 145-1-C1.jpg)
     */
    protected function groupImagesByProductCode(array $images): array
    {
        $grouped = [];

        foreach ($images as $imagePath) {
            $filename = basename($imagePath);

            // Intentar patrón 1: [1-2]-[CODIGO][-C[COLOR]]?.jpg
            if (preg_match('/^[12]-(.+?)(-C\d+)?\.jpg$/', $filename, $matches)) {
                $productCode = $matches[1];
                $colorCode = isset($matches[2]) ? ltrim($matches[2], '-') : null;

                if (!isset($grouped[$productCode])) {
                    $grouped[$productCode] = [];
                }

                $grouped[$productCode][] = [
                    'path' => $imagePath,
                    'color' => $colorCode,
                    'filename' => $filename,
                ];
            }
            // Intentar patrón 2: [CODIGO]-[NUMERO][-C[COLOR]]?.jpg
            elseif (preg_match('/^(.+?)-(\d+)(-C\d+)?\.jpg$/', $filename, $matches)) {
                $productCode = $matches[1];
                $colorCode = isset($matches[3]) ? ltrim($matches[3], '-') : null;

                if (!isset($grouped[$productCode])) {
                    $grouped[$productCode] = [];
                }

                $grouped[$productCode][] = [
                    'path' => $imagePath,
                    'color' => $colorCode,
                    'filename' => $filename,
                ];
            }
        }

        return $grouped;
    }

    /**
     * Extraer nombre de marca del nombre de la carpeta
     * Ej: "Cambidge (YG11045 8875)" -> "Cambidge"
     */
    protected function extractBrandFromFolderName(string $folderName): string
    {
        // Extraer el nombre antes del primer paréntesis o espacio
        if (preg_match('/^([^\(]+?)(?:\s*\(|$)/', $folderName, $matches)) {
            return trim($matches[1]);
        }

        // Si no hay paréntesis, usar la primera palabra
        $words = explode(' ', $folderName);
        return $words[0] ?? $folderName;
    }

    /**
     * Crear producto desde un grupo de imágenes
     */
    protected function createProductFromImages(string $productCode, array $imagesData, Brand $brand, Category $category, string $folderName): void
    {
        // Generar SKU único
        $sku = strtoupper(str_replace([' ', '-'], '', $productCode));

        // Generar slug único
        $slug = $this->generateUniqueSlug($brand->name, $sku, $productCode);

        // Obtener colores únicos para el nombre
        $colors = array_unique(array_filter(array_column($imagesData, 'color')));
        $colorSuffix = !empty($colors) ? ' (' . implode(', ', $colors) . ')' : '';

        // Crear el producto
        $product = Product::create([
            'sku' => $sku,
            'name' => "{$productCode}{$colorSuffix}",
            'slug' => $slug,
            'description' => "{$productCode} - Modelo exclusivo de {$brand->name}",
            'text' => "Descubre {$productCode}, parte de la colección de {$category->name} de {$brand->name}. Diseño elegante y materiales de alta calidad.",
            'price' => $this->generatePriceForBrand($brand->name),
            'list_price' => $this->generatePriceForBrand($brand->name) * 1.25,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'is_featured' => rand(0, 10) === 0, // 10% de chance de ser destacado
            'sort_order' => rand(0, 100),
        ]);

        // Procesar imágenes con orden
        foreach ($imagesData as $index => $imageData) {
            $this->processImageForProduct(
                $imageData['path'],
                $product,
                $index,
                $imageData['color']
            );
        }
    }
}
