<?php

namespace App\Providers;

use App\Models\Block;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class BlockServiceProvider extends ServiceProvider
{
    protected ProductService $productService;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->productService = app(ProductService::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // View composer para el bloque categories
        View::composer('blocks.categories', function ($view) {
            $categories = Category::active()->ordered()->take(4)->get();

            $view->with([
                'categories' => $categories,
            ]);
        });

        // View composer para el bloque products (Target Eyewear)
        View::composer('blocks.products', function ($view) {
            $block = $view->getData()['block'] ?? null;

            // Solo cargar categorías y marcas para los filtros
            // Los productos se cargan via AJAX
            $categories = Category::active()->ordered()->get();
            $brands = Brand::active()->ordered()->get();

            $view->with([
                'categories' => $categories,
                'brands' => $brands,
            ]);
        });

        // View composer para el bloque products_grid
        View::composer('blocks.products_grid', function ($view) {
            $block = $view->getData()['block'] ?? null;

            if (!$block) {
                return;
            }

            // Obtener parámetros del bloque
            $itemsPerPage = $block->data['items_per_page'] ?? 12;
            $showFilters = $block->data['show_filters'] ?? true;
            $showPagination = $block->data['show_pagination'] ?? true;
            $title = $block->data['title'] ?? 'Productos';

            // Obtener filtros de la URL
            $categorySlug = request()->query('category');
            $brandSlug = request()->query('brand');
            $search = request()->query('search');
            $sort = request()->query('sort', 'name');

            // Construir consulta
            $query = Product::active()->with(['category', 'brand', 'media' => function($q) {
                $q->orderBy('sort_order');
            }]);

            // Aplicar filtros
            $currentCategory = null;
            if ($categorySlug) {
                $category = Category::where('slug', $categorySlug)->where('is_active', true)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                    $currentCategory = $category;
                }
            }

            if ($brandSlug) {
                $brand = Brand::where('slug', $brandSlug)->where('is_active', true)->first();
                if ($brand) {
                    $query->where('brand_id', $brand->id);
                }
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Aplicar ordenamiento
            switch ($sort) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'code':
                    $query->orderBy('code', 'asc');
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }

            // Paginar resultados
            $products = $query->paginate($itemsPerPage);
            $products->appends([
                'category' => $categorySlug,
                'brand' => $brandSlug,
                'search' => $search,
                'sort' => $sort,
            ]);

            $view->with([
                'products' => $products,
                'categories' => Category::where('is_active', true)->orderBy('name')->get(),
                'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
                'currentCategory' => $currentCategory,
                'categorySlug' => $categorySlug,
                'brandSlug' => $brandSlug,
                'search' => $search,
                'sort' => $sort,
                'showFilters' => $showFilters,
                'showPagination' => $showPagination,
                'title' => $title,
            ]);
        });

        // View composer para el bloque featured_products
        View::composer('blocks.featured_products', function ($view) {
            $featuredProducts = Product::active()
                ->featured()
                ->with(['category', 'brand', 'media'])
                ->ordered()
                ->take(4)
                ->get();

            $view->with([
                'featuredProducts' => $featuredProducts,
            ]);
        });
    }
}
