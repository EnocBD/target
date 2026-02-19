<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with(['media' => function($query) {
                $query->orderBy('sort_order');
            }, 'category', 'brand'])
            ->firstOrFail();

        // Get main image for the product
        $mainImage = $this->productService->getMainImage($product);

        // Obtener productos similares de la misma categoría (máx 4, excluyendo el actual)
        $similarProducts = collect();

        if ($product->category) {
            $similarProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->active()
                ->ordered()
                ->with(['media' => function($query) {
                    $query->orderBy('sort_order');
                }, 'category'])
                ->limit(4)
                ->get();
        }

        return view('public.product', compact('product', 'mainImage', 'similarProducts'));
    }

    /**
     * Filtrar productos vía AJAX
     */
    public function filter(Request $request)
    {
        $search = $request->input('q');
        $categories = $request->input('categories', []);
        $brands = $request->input('brands', []);
        $page = $request->input('page', 1);
        $itemsPerPage = $request->input('items_per_page', 12);

        // Construir consulta
        $query = Product::active()->with(['category', 'brand', 'media' => function($q) {
            $q->orderBy('sort_order');
        }]);

        // Aplicar filtros de categorías (múltiples)
        if (!empty($categories)) {
            $categoryIds = \App\Models\Category::whereIn('slug', $categories)
                ->where('is_active', true)
                ->pluck('id');
            if ($categoryIds->isNotEmpty()) {
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Aplicar filtros de marcas (múltiples)
        if (!empty($brands)) {
            $brandIds = \App\Models\Brand::whereIn('slug', $brands)
                ->where('is_active', true)
                ->pluck('id');
            if ($brandIds->isNotEmpty()) {
                $query->whereIn('brand_id', $brandIds);
            }
        }

        // Aplicar búsqueda de texto (títulos, categorías, marcas)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('category', function($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('brand', function($brandQuery) use ($search) {
                      $brandQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento por defecto
        $query->orderBy('name', 'asc');

        // Paginar resultados
        $products = $query->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'products' => view('blocks.partials.product-grid', compact('products'))->render(),
            'pagination' => $products->hasPages()
                ? view('blocks.partials.pagination-grid', [
                    'products' => $products,
                    'search' => $search,
                    'categories' => $categories,
                    'brands' => $brands
                ])->render()
                : '',
            'count' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ]);
    }
}
