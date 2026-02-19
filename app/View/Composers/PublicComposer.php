<?php
// app/View/Composers/PublicComposer.php

namespace App\View\Composers;

use App\Models\Page;
use App\Models\Setting;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicComposer
{
    public function compose(View $view)
    {
        // Traer páginas activas agrupadas por posición de menú
        // Menú principal: incluye páginas con posición 'main' y 'both'
        $mainMenu = Page::active()
            ->whereIn('menu_position', ['main', 'both'])
            ->whereNull('parent_id')
            ->ordered()
            ->with(['children' => function($query) {
                $query->where('is_active', true)->ordered();
            }])
            ->get();

        // Footer: incluye páginas con posición 'footer' y 'both'
        $footerMenu = Page::active()
            ->whereIn('menu_position', ['footer', 'both'])
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        $settings = Cache::remember('settings', 3600, function () {
            return Setting::all()->pluck('value', 'key');
        });

        $settings = (object) $settings->toArray();

        // Traer marcas y categorías
        $brands = Cache::remember('brands_active', 3600, function () {
            return Brand::active()->orderBy('sort_order')->orderBy('name')->get();
        });

        $categories = Cache::remember('categories_active', 3600, function () {
            return Category::orderBy('sort_order')->orderBy('name')->get();
        });

        // Pasar datos a la vista
        $view->with([
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
            'settings' => $settings,
            'brands' => $brands,
            'categories' => $categories,
        ]);
    }
}
