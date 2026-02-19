<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use HasMedia;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        $this->middleware('auth');
        $this->middleware('permission:view-products')->only(['index', 'show']);
        $this->middleware('permission:create-products')->only(['create', 'store']);
        $this->middleware('permission:edit-products')->only(['edit', 'update']);
        $this->middleware('permission:delete-products')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Product::with(['media' => function($query) {
            $query->where('type', 'image')->orderBy('sort_order');
        }])->with(['category', 'brand']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('brand', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        return view('admin.products.form', compact('product'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active');
        $validated['order'] = $validated['order'] ?? 0;

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        $product->load(['media' => function($query) {
            $query->orderBy('sort_order');
        }]);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['media' => function($query) {
            $query->orderBy('sort_order');
        }]);
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active');
        $validated['order'] = $validated['order'] ?? 0;

        // Ensure unique slug (except for current product)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        // Delete associated media files
        foreach ($product->media as $media) {
            $this->mediaService->deleteMedia($media);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
