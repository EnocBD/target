<?php
// app/View/Composers/PublicComposer.php

namespace App\View\Composers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicComposer
{
    public function compose(View $view)
    {
        $mainMenu = Cache::remember('menu.main', 900, function () {
            return Page::active()
                ->whereIn('menu_position', ['main', 'both'])
                ->whereNull('parent_id')
                ->ordered()
                ->with(['children' => function($query) {
                    $query->where('is_active', true)->ordered();
                }])
                ->get();
        });

        $footerMenu = Cache::remember('menu.footer', 900, function () {
            return Page::active()
                ->whereIn('menu_position', ['footer', 'both'])
                ->whereNull('parent_id')
                ->ordered()
                ->get();
        });

        $settings = Cache::remember('settings', 900, function () {
            return Setting::all()->pluck('value', 'key');
        });

        $settings = (object) $settings->toArray();

        $view->with([
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
            'settings' => $settings,
        ]);
    }
}