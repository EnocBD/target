<?php

// Add this to your AppServiceProvider.php boot() method or create a separate helper file

if (!function_exists('render_page_blocks')) {
    /**
     * Render all blocks for a given page
     *
     * @param int $pageId
     * @return string
     */
    function render_page_blocks($pageId)
    {
        $blocks = \App\Models\Block::where('page_id', $pageId)
            ->whereNull('block_id') // Only parent blocks
            ->orderBy('position')
            ->get();

        $output = '';
        foreach ($blocks as $block) {
            $output .= \App\Http\Controllers\Admin\BlockController::renderBlock($block);
        }

        return $output;
    }
}

if (!function_exists('render_block_by_layout')) {
    /**
     * Render a single block by layout type with custom data
     *
     * @param string $layout
     * @param array $data
     * @param string|null $imagePath
     * @return string
     */
    function render_block_by_layout($layout, $data = [], $imagePath = null)
    {
        // Create a temporary block object
        $block = new \App\Models\Block();
        $block->layout = $layout;
        $block->text = (object) $data;
        $block->image_path = $imagePath;

        return \App\Http\Controllers\Admin\BlockController::renderBlock($block);
    }
}

if (!function_exists('is_external_url')) {
    /**
     * Check if a URL is external (contains http:// or https://)
     *
     * @param string $url
     * @return bool
     */
    function is_external_url($url)
    {
        if (empty($url)) {
            return false;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}

if (!function_exists('link_target')) {
    /**
     * Get the target attribute for a link (blank for external, null for internal)
     *
     * @param string $url
     * @return string|null
     */
    function link_target($url)
    {
        return is_external_url($url) ? '_blank' : null;
    }
}

if (!function_exists('tel')) {
    /**
     * Clean phone number to use in tel: links (remove all non-numeric characters)
     *
     * @param string $phone
     * @return string
     */
    function tel($phone)
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all characters except numbers and +
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}