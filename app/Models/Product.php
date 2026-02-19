<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'text',
        'description',
        'price',
        'slug',
        'category_id',
        'brand_id',
        'list_price',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'float',
        'list_price' => 'float',
    ];

    /**
     * Get category for the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get brand for the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get all media for the product.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Get images for the product.
     */
    public function images(): MorphMany
    {
        return $this->media()->where('type', 'image');
    }

    /**
     * Get videos for the product.
     */
    public function videos(): MorphMany
    {
        return $this->media()->where('type', 'video');
    }

    /**
     * Get documents for the product.
     */
    public function documents(): MorphMany
    {
        return $this->media()->where('type', 'document');
    }

    /**
     * Get main image for the product.
     */
    public function getMainImageAttribute(): ?Media
    {
        // If media is already loaded, use it to avoid N+1 queries
        if ($this->relationLoaded('media')) {
            return $this->media->firstWhere('type', 'image');
        }

        return $this->images()->orderBy('sort_order')->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get URL for the product page.
     */
    public function getUrlAttribute(): string
    {
        return route('products.show', $this->slug);
    }
}
