<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\View\Composers\PublicComposer;
use App\Services\HtmlSanitizerService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar servicio de sanitización HTML
        $this->app->singleton(HtmlSanitizerService::class, function ($app) {
            $service = new HtmlSanitizerService();
            $service->ensureCacheDirectory();
            return $service;
        });
    }

    public function boot()
    {
        // Use Bootstrap for pagination
        Paginator::useBootstrap();

        // Force HTTPS only in production or when not localhost
        if (config('app.env') === 'production' || !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'])) {
            URL::forceScheme('https');
        }

        // Blade directive for reCAPTCHA
        \Blade::directive('captcha', function () {
            $siteKey = env('RECAPTCHA_SITE_KEY');
            if (!$siteKey) {
                return '<!-- reCAPTCHA not configured: RECAPTCHA_SITE_KEY not set -->';
            }
            return '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"></div>';
        });

        View::composer([
            'layouts.public',
            'layouts.public',
            'public.*',
            'blocks.*'
        ], PublicComposer::class);
    }
}