<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Media;

class ProductService
{
    /**
     * Get the main image for a product
     */
    public function getMainImage(Product $product): ?Media
    {
        if (!$product->relationLoaded('media')) {
            $product->load('media');
        }

        return $product->media->sortBy('sort_order')->first();
    }

    /**
     * Get main image URL for a product
     */
    public function getMainImageUrl(Product $product): ?string
    {
        $image = $this->getMainImage($product);

        if (!$image) {
            return null;
        }

        return $image->thumbnail_url ?? $image->file_url;
    }

    /**
     * Get products with media for display
     */
    public function getProductsWithMedia($query)
    {
        return $query->with(['media' => function($query) {
            $query->orderBy('sort_order');
        }]);
    }
}
