# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Building and Development
- `composer dev` - Start development server (Laravel, Vite, queue, logs in parallel)
- **Note:** You must run `npm run dev` or `npm run build` manually - Claude will not execute npm/yarn commands
- `php artisan serve --no-reload` - Start Laravel backend only

### Database
- `php artisan migrate` - Run all migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeders
- `php artisan make:migration` - Create new migration
- `php artisan db:seed` - Run seeders

### Testing
- `composer test` - Run full test suite
- `php artisan test` - Run PHPUnit tests

### Cache and Config
- `php artisan config:clear` - Clear config cache
- `php artisan cache:clear` - Clear application cache
- `php artisan view:clear` - Clear compiled views
- `php artisan route:clear` - Clear route cache

### Generators
- `php artisan make:controller` - Create new controller
- `php artisan make:model` - Create new model
- `php artisan make:seeder` - Create new seeder
- `php artisan make:command` - Create new Artisan command

## Architecture Overview

This is a **block-based CMS with e-commerce functionality** built on Laravel 12. The core architecture revolves around:

1. **Pages** as containers for **Blocks** (reusable content components)
2. **Products/Categories/Brands** with polymorphic **Media** relationships
3. **Cart/Checkout** via `lukepolo/laracart` package (WhatsApp-based, no payment gateway)
4. Complete separation of **Admin** and **Public** controllers
5. Configuration-driven block system defined in `/config/blocks.php`

### Core Models and Relationships

**Product** (`app/Models/Product.php`)
- `belongsTo(Category)`, `belongsTo(Brand)`, `morphMany(Media)`
- Fields: sku, name, price, list_price, slug, is_active, is_featured
- Accessors: `main_image`, `url`
- Scopes: `active()`, `featured()`, `ordered()`

**Category** (`app/Models/Category.php`)
- `hasMany(Product)`, `morphMany(Media)`
- Fields: name, slug, excerpt, description, is_active, sort_order

**Brand** (`app/Models/Brand.php`)
- `hasMany(Product)`
- Fields: code, name, slug, description, image

**Page** (`app/Models/Page.php`)
- `hasMany(Block)`, self-referential parent/child relationships
- Fields: title, slug, meta_title, meta_description, is_active, menu_position, parent_id
- Auto-increments `sort_order` on creation

**Block** (`app/Models/Block.php`)
- `belongsTo(Page)`, self-referential parent/child (nested blocks)
- Fields: page_id, parent_id, block_type, data (JSON), styles (JSON), image_path
- Key methods: `getDataValue()`, `getComputedStyle()`, `getDataAsArray()`
- Supports nested repeaters with file uploads

**Media** (`app/Models/Media.php`)
- Polymorphic: can belong to Product, Category, Brand, etc.
- Fields: mediable_type, mediable_id, type ('image', 'video', 'document'), file, thumbnail
- Accessors: `file_url`, `thumbnail_url`
- Type checkers: `isImage()`, `isVideo()`, `isDocument()`

**Setting** (`app/Models/Setting.php`)
- Key-value store for global configuration
- Static methods: `get()`, `set()`, `getAllGrouped()`

### Block System

Block types are configuration-driven in `/config/blocks.php`. Each block defines:

```php
'block_name' => [
    'name' => 'Human Readable Name',
    'description' => 'Block description',
    'fields' => [
        'field_name' => [
            'type' => 'text|textarea|editor|image_upload|repeater|group|select|checkbox',
            'label' => 'Field Label',
            'required' => true|false,
            'options' => [...],  // for select fields
        ]
    ]
]
```

**Available Block Types:**
- `slides` - Image carousel with captions (desktop + mobile images)
- `categories` - Category grid display
- `featured_products` - Featured product showcase
- `products` - Full product catalog with filters
- `steps` - Step-by-step guide (repeater)
- `text` - Rich text content with Quill editor
- `contact_info` - Contact information display
- `contact_form` - Contact form with Leaflet map

**Block Rendering:**
- `render_page_blocks($pageId)` - Render all blocks for a page
- `render_block_by_layout($layout, $data, $imagePath)` - Render single block with custom data
- Blocks rendered via `BlockController::renderBlock()`
- Templates in `/resources/views/blocks/{layout}.blade.php`

**Block Preview System:**
- `preview()` - Quick preview of block data
- `iframePreview()` - Full-page preview with site layout
- `savePreview()` - Save to `data_preview` field
- `saveFromPreview()` - Commit preview to actual block
- Uses `BlockPreview` model for temporary storage
- Image placeholders for preview mode (`__IMAGE_fieldname__`)

### Cart/Checkout System (lukepolo/laracart)

**CartController** (`app/Http/Controllers/CartController.php`)

Session-based cart with AJAX operations. WhatsApp-based order processing (no payment gateway).

**Key Operations:**
- `add()` - Add product to cart via `LaraCart::add()`
- `update()` - Update item quantity
- `remove()` - Remove item from cart
- `clear()` - Empty cart
- `api()` - Unified AJAX endpoint for all operations
- `checkout()` - Display checkout page
- `processOrder()` - Build WhatsApp message and redirect

**Cart Data Structure:**
```php
LaraCart::add(
    $product->id,        // itemID
    $product->name,      // name
    $qty,                // qty
    $product->price,     // price
    [                    // options (sku, slug, brand, list_price)
        'sku' => $product->sku,
        'slug' => $product->slug,
        'brand' => $product->brand->name,
        'list_price' => $product->list_price
    ],
    true,                // taxable
    false                // lineItem
);
```

**AJAX API:**
```
POST /cart/api
{
    "action": "add|update|remove|clear|get",
    "product_id": 123,
    "qty": 2,
    "item_id": "hash"
}

Response: {success, items, count, total, subTotal, tax}
```

### Admin vs Public Controllers

**Admin Controllers** (`app/Http/Controllers/Admin/`)
- Protected by `auth` middleware
- Use Spatie `laravel-permission` for granular access control
- Full CRUD operations with media upload/management
- Controllers: AdminController, PageController, BlockController, ProductController, CategoryController, BrandController, SettingController, ImageUploadController, FormSubmissionController, UserController
- `HasMedia` trait for reusable media management

**Public Controllers** (`app/Http/Controllers/`)
- `PublicController` - Dynamic page rendering via `/{slug}` route
- `ProductController` - Product catalog and filtering
- `CartController` - Shopping cart operations
- `FormController` - Contact form submissions

**Route Structure** (`routes/web.php`):
```php
Route::prefix('admin')->middleware(['auth'])->group(function() {
    Route::resource('pages', PageController::class);
    Route::resource('blocks', BlockController::class);
    // ... other admin routes
});

Route::get('/{slug?}', [PublicController::class, 'showPage']);
Route::get('/productos/{slug}', [ProductController::class, 'show']);
Route::prefix('cart')->group(function() {
    Route::get('/', [CartController::class, 'index']);
    Route::post('api', [CartController::class, 'api']);
    Route::get('checkout', [CartController::class, 'checkout']);
});
```

### Media Handling System

**MediaService** (`app/Services/MediaService.php`)
- Image processing via Intervention Image
- Resize main image to max 1080px (only if larger)
- Generate thumbnail at 576px width (only if larger)
- JPEG compression at 60% quality
- Storage: `storage/app/public/media/{ModelType}/{model_id}/`

**HasMedia Trait** (`app/Http/Controllers/Admin/HasMedia.php`)
- Reusable trait for controllers with media
- Methods: `uploadMedia()`, `deleteMedia()`, `reorderMedia()`, `resolveModel()`

### Helper Functions (`app/helpers.php`)

- `render_page_blocks($pageId)` - Render all blocks for a page
- `render_block_by_layout($layout, $data, $imagePath)` - Render single block with custom data
- `is_external_url($url)` - Check if URL starts with http:// or https://
- `link_target($url)` - Returns '_blank' for external URLs, null for internal
- `tel($phone)` - Clean phone number for tel: links (removes non-numeric)

### Key Services

**AppServiceProvider** (`app/Providers/AppServiceProvider.php`)
- Registers `HtmlSanitizerService` (XSS prevention via HTML Purifier)
- Blade directive: `@captcha` for Google reCAPTCHA
- View composers for settings and admin data
- Bootstrap pagination, HTTPS enforcement

**HtmlSanitizerService** - Smart sanitization for different contexts (admin vs public)

**MediaService** - Centralized image processing and storage

## Important Notes

### Currency Display
**Known Issue:** Some checkout pages still display prices in Guaraníes instead of the active currency. The checkout total (`#checkout-total` in `checkout/index.blade.php`) and shipping cost selection may not respect the current currency setting.

### Adding New Block Types

1. Add configuration to `/config/blocks.php`
2. Create Blade template at `/resources/views/blocks/{layout}.blade.php`
3. Optionally add preview-specific logic if needed

### Security Features

- XSS prevention via HTML Purifier
- CSRF protection on all forms
- Spatie permissions for role-based access control
- Rate limiting on form submissions (`rate.limit/forms:10,1`)
- File upload validation (MIME type, size limits)
- Google reCAPTCHA integration

### Multi-language Support

The application supports multiple locales via `laravel-lang` packages:
- Configuration: `APP_LOCALE` and `APP_FALLBACK_LOCALE` in `.env`
- Language files in `/lang/{locale}/`

### Database

**This project uses MySQL.** If database operations fail, ask the user to verify:
- MySQL server is running
- Database credentials in `.env` are correct (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST`, `DB_PORT`)
- Database exists and user has proper permissions
