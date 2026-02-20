<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'file',
        'thumbnail',
        'original_name',
        'title',
        'mime_type',
        'size',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'file_url',
        'thumbnail_url',
    ];

    /**
     * Get the parent mediable model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if media is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if media is a document
     */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute(): string
    {
        return asset($this->file);
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset($this->thumbnail) : null;
    }
}
