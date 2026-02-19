<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $fillable = [
        'page_id',
        'parent_id',
        'block_type',
        'data',
        'data_preview',
        'styles',
        'image_path',
        'thumbnail_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'data' => 'object',
        'data_preview' => 'object',
        'styles' => 'object',
        'is_active' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function parent()
    {
        return $this->belongsTo(Block::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Block::class, 'parent_id')->orderBy('sort_order');
    }

    public function getDataValue(string $key, $default = null)
    {
        if (!$this->data) {
            return $default;
        }

        if (is_object($this->data)) {
            return $this->data->{$key} ?? $default;
        }

        if (is_array($this->data)) {
            return $this->data[$key] ?? $default;
        }

        return $default;
    }

    public function getRawDataValue(string $key, $default = '')
    {
        $value = $this->getDataValue($key, $default);

        if (is_string($value)) {
            return $value;
        }

        return $default;
    }

    public function getEscapedDataValue(string $key, $default = '')
    {
        $value = $this->getRawDataValue($key, $default);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function getDataAsArray()
    {
        if (!$this->data) {
            return [];
        }

        if (is_object($this->data)) {
            return json_decode(json_encode($this->data), true);
        }

        if (is_array($this->data)) {
            return $this->data;
        }

        return [];
    }

    public function getStyleValue(string $key, $default = null)
    {
        if (!$this->styles) {
            return $default;
        }

        if (is_object($this->styles)) {
            return $this->styles->{$key} ?? $default;
        }

        if (is_array($this->styles)) {
            return $this->styles[$key] ?? $default;
        }

        return $default;
    }

    public function getStylesAsArray()
    {
        if (!$this->styles) {
            return [];
        }

        if (is_object($this->styles)) {
            return json_decode(json_encode($this->styles), true);
        }

        if (is_array($this->styles)) {
            return $this->styles;
        }

        return [];
    }

    public function getComputedStyles()
    {
        $styles = $this->getStylesAsArray();
        $css = [];

        // Padding
        if (!empty($styles['padding_top'])) {
            $css[] = "padding-top: {$styles['padding_top']}";
        }
        if (!empty($styles['padding_right'])) {
            $css[] = "padding-right: {$styles['padding_right']}";
        }
        if (!empty($styles['padding_bottom'])) {
            $css[] = "padding-bottom: {$styles['padding_bottom']}";
        }
        if (!empty($styles['padding_left'])) {
            $css[] = "padding-left: {$styles['padding_left']}";
        }

        // Margin
        if (!empty($styles['margin_top'])) {
            $css[] = "margin-top: {$styles['margin_top']}";
        }
        if (!empty($styles['margin_right'])) {
            $css[] = "margin-right: {$styles['margin_right']}";
        }
        if (!empty($styles['margin_bottom'])) {
            $css[] = "margin-bottom: {$styles['margin_bottom']}";
        }
        if (!empty($styles['margin_left'])) {
            $css[] = "margin-left: {$styles['margin_left']}";
        }

        // Background
        if (!empty($styles['background_color'])) {
            $css[] = "background-color: {$styles['background_color']}";
        }
        if (!empty($styles['background_image'])) {
            $css[] = "background-image: url('{$styles['background_image']}')";
        }
        if (!empty($styles['background_size'])) {
            $css[] = "background-size: {$styles['background_size']}";
        }
        if (!empty($styles['background_position'])) {
            $css[] = "background-position: {$styles['background_position']}";
        }
        if (!empty($styles['background_repeat'])) {
            $css[] = "background-repeat: {$styles['background_repeat']}";
        }

        // Text Color
        if (!empty($styles['color'])) {
            $css[] = "color: {$styles['color']}";
        }

        // Border
        if (!empty($styles['border_width'])) {
            $css[] = "border-width: {$styles['border_width']}";
        }
        if (!empty($styles['border_style'])) {
            $css[] = "border-style: {$styles['border_style']}";
        }
        if (!empty($styles['border_color'])) {
            $css[] = "border-color: {$styles['border_color']}";
        }
        if (!empty($styles['border_radius'])) {
            $css[] = "border-radius: {$styles['border_radius']}";
        }

        // Custom CSS
        if (!empty($styles['custom_css'])) {
            $css[] = $styles['custom_css'];
        }

        return !empty($css) ? implode('; ', $css) : '';
    }
}
