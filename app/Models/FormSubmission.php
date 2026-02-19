<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_type',
        'form_data', 
        'uploaded_files',
        'status',
        'notes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'form_data' => 'array',
        'uploaded_files' => 'array'
    ];

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('form_type', $type);
    }

    public function getFormDataAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getUploadedFilesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }
}
