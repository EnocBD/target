<?php
// app/Http/Controllers/PublicController.php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function showPage($slug = '/')
    {
        $segments = explode('/', $slug);
        $slug = $segments[0];
        $params = array_slice($segments, 1);

        // Normalize slug for homepage
        if ($slug === '' || $slug === '/') {
            $slug = '/';
        }

        // Find page by slug
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            abort(404);
        }

        // Load blocks with their data
        $page->load(['blocks' => function($query) {
            $query->orderBy('sort_order');
        }]);

        // Obtener settings para el layout
        $settings = (object) Setting::all()->pluck('value', 'key')->toArray();

        $params = [
            'page' => $page,
            'view' => $slug,
            'params' => $params,
            'settings' => $settings
        ];

        return view('public.page', $params);
    }

    public function sitemap()
    {
        $pages = Page::where('is_active', true)
            ->where('slug', '!=', '/404')
            ->orderBy('updated_at', 'desc')
            ->get();

        $settings = (object) Setting::all()->pluck('value', 'key')->toArray();

        return response()->view('sitemap', [
            'pages' => $pages,
            'settings' => $settings
        ])->header('Content-Type', 'text/xml');
    }

    public function temp(Request $request)
    {
        $success = $request->session()->get('success');
        return view('temp', compact('success'));
    }

    public function tempSubmit(Request $request)
    {
        return redirect()->route('temp.show')->with('success', 'Enviado exitosamente');
    }
}