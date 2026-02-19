<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProductController as PublicProductController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Auth::routes([
    'register' => true,
    'reset' => true
]); // Enable registration and password reset

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Pages and Blocks
    Route::resource('pages', PageController::class)->except(['show']);
    Route::post('pages/update-order', [PageController::class, 'updateOrder'])->name('pages.update-order');

    // Rutas específicas de bloques (DEBEN ir antes del resource)
    Route::post('blocks/update-order', [BlockController::class, 'updateOrder'])->name('blocks.update-order');
    Route::post('blocks/quill-image', [BlockController::class, 'uploadQuillImage'])->name('blocks.quill-image');
    Route::post('blocks/preview', [BlockController::class, 'preview'])->name('blocks.preview');
    Route::get('blocks/iframe-preview/{blockId?}', [BlockController::class, 'iframePreview'])->name('blocks.iframe-preview');

    // Rutas de previsualización con nombres nuevos
    Route::put('blocks/preview-save', [BlockController::class, 'savePreview'])->name('blocks.preview-save');
    Route::get('blocks/preview-show/{previewId?}', [BlockController::class, 'showPreview'])->name('blocks.preview-show');
    Route::post('blocks/preview-commit', [BlockController::class, 'saveFromPreview'])->name('blocks.preview-commit');

    // Resource routes (DEBEN ir después de las rutas específicas)
    Route::resource('blocks', BlockController::class)->except(['show']);

    // Users (only for admins)
    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:view-users');

    // Products
    Route::resource('products', ProductController::class);

    // Media management for products
    Route::post('products/{product}/media', [ProductController::class, 'uploadProductMedia'])
        ->name('products.media.upload');
    Route::delete('products/{product}/media/{mediaId}', [ProductController::class, 'deleteProductMedia'])
        ->name('products.media.delete');
    Route::post('products/{product}/media/reorder', [ProductController::class, 'reorderProductMedia'])
        ->name('products.media.reorder');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Media management for categories
    Route::post('categories/{category}/media', [CategoryController::class, 'uploadMedia'])
        ->name('categories.media.upload');
    Route::delete('categories/{category}/media/{mediaId}', [CategoryController::class, 'deleteMedia'])
        ->name('categories.media.delete');
    Route::post('categories/{category}/media/reorder', [CategoryController::class, 'reorderMedia'])
        ->name('categories.media.reorder');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::post('/upload-image', [ImageUploadController::class, 'upload'])
        ->name('upload.image');
    Route::delete('/delete-image', [ImageUploadController::class, 'delete'])
        ->name('delete.image');

    // Form submissions
    Route::get('forms', [FormSubmissionController::class, 'index'])->name('forms.index');
    Route::get('forms/{submission}', [FormSubmissionController::class, 'show'])->name('forms.show');
    Route::patch('forms/{submission}/mark-read', [FormSubmissionController::class, 'markAsRead'])->name('forms.mark-read');
    Route::patch('forms/{submission}/archive', [FormSubmissionController::class, 'archive'])->name('forms.archive');
    Route::patch('forms/{submission}/notes', [FormSubmissionController::class, 'updateNotes'])->name('forms.update-notes');
    Route::delete('forms/{submission}', [FormSubmissionController::class, 'destroy'])->name('forms.destroy');
});

// Sitemap
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('add', [CartController::class, 'add'])->name('add');
    Route::post('update', [CartController::class, 'update'])->name('update');
    Route::post('remove', [CartController::class, 'remove'])->name('remove');
    Route::post('clear', [CartController::class, 'clear'])->name('clear');
    Route::get('checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('checkout', [CartController::class, 'processOrder'])->name('process');

    // Single API endpoint for all cart operations
    Route::post('api', [CartController::class, 'api'])->name('api');
});

// Rutas específicas para productos (precedencia sobre rutas dinámicas)
Route::get('/productos/{slug}', [PublicProductController::class, 'show'])->name('products.show');
Route::post('/productos/filter', [PublicProductController::class, 'filter'])->name('products.filter');

// Form submission routes
Route::post('/form/submit', [FormController::class, 'submit'])
    ->middleware('rate.limit.forms:10,1')
    ->name('form.submit');

// Rutas dinámicas de páginas
Route::get('/{slug?}', [PublicController::class, 'showPage'])->name('page.show')->where('slug', '.*');
