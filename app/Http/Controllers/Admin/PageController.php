<?php
// app/Http/Controllers/Admin/PageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-pages')->only(['index', 'show']);
        $this->middleware('permission:create-pages')->only(['create', 'store']);
        $this->middleware('permission:edit-pages')->only(['edit', 'update']);
        $this->middleware('permission:delete-pages')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Page::with('blocks')->ordered();

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $pages = $query->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new Page(); // Instancia vacía
        $pages = Page::where('is_active', true)->pluck('title', 'id')->toArray();
        return view('admin.pages.form', compact('page', 'pages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) {
                    $query->where('slug', '<>', '#');
                }),
            ],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:pages,id',
            'menu_position' => 'nullable|in:main,footer,both'
        ]);

        $page = Page::create($validated);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Página creada exitosamente.');
    }

    public function edit(Page $page)
    {
        $pages = Page::where('is_active', true)->where('id', '!=', $page->id)->pluck('title', 'id')->toArray();
        return view('admin.pages.form', compact('page', 'pages'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->ignore($page->id)->where(function ($query) {
                    $query->where('slug', '<>', '#');
                }),
            ],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:pages,id',
            'menu_position' => 'nullable|in:main,footer,both'
        ]);

        $page->update($validated);

        return back()->with('success', 'Página actualizada exitosamente.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Página eliminada exitosamente.');
    }

    public function updateOrder(Request $request)
    {
        $data = $request->all(); // [{id, sort_order}, ...]
        foreach($data as $item) {
            Page::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['status' => 'ok']);
    }
}