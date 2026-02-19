<?php
// app/Models/Page.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
        'parent_id',
        'menu_position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($page) {
            if (is_null($page->sort_order)) {
                $page->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function getIsExternalAttribute(): bool
    {
        return str_starts_with($this->slug, 'http://') || str_starts_with($this->slug, 'https://');
    }
}
