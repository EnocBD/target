<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'excerpt',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all media for the category.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Get images for the category.
     */
    public function images(): MorphMany
    {
        return $this->media()->where('type', 'image');
    }

    /**
     * Get main image for the category.
     */
    public function getMainImageAttribute(): ?Media
    {
        if ($this->relationLoaded('media')) {
            return $this->media->firstWhere('type', 'image');
        }

        return $this->images()->orderBy('sort_order')->first();
    }

    public function firstProduct(): ?Product
    {
        return $this->products()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}