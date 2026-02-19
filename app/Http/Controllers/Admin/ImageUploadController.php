<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;

class ImageUploadController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'mimes:jpeg,png,jpg,gif,webp,svg|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid file format or size too large'
            ], 422);
        }

        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            try {
                // Get original extension
                $originalExtension = strtolower($file->getClientOriginalExtension());

                // Handle SVG files differently (no image processing needed)
                if ($originalExtension === 'svg') {
                    // Generate unique filename for SVG
                    $filename = time() . '_' . Str::random(10) . '.svg';
                    $path = 'editor-images/' . $filename;

                    // Store SVG directly without processing
                    Storage::disk('public')->putFileAs('editor-images', $file, $filename);

                    // Get full URL
                    $url = Storage::url($path);

                    $uploadedFiles[] = [
                        'src' => $url,
                        'type' => 'image',
                        'height' => 'auto',
                        'width' => 'auto'
                    ];
                } else {
                    // Process raster images with Intervention Image
                    $image = $this->imageManager->read($file->getRealPath());

                    // Only resize if image is wider than 1920px (no upsizing)
                    if ($image->width() > 1920) {
                        $image->scale(width: 1920);
                    }

                    // Determine output format and extension
                    $outputExtension = $this->getOutputExtension($originalExtension);

                    // Generate unique filename with correct extension
                    $filename = time() . '_' . Str::random(10) . '.' . $outputExtension;

                    // Encode image based on format
                    $encodedImage = $this->encodeImage($image, $originalExtension);

                    // Store the processed image
                    $path = 'editor-images/' . $filename;
                    Storage::disk('public')->put($path, $encodedImage);

                    // Get full URL
                    $url = Storage::url($path);

                    $uploadedFiles[] = [
                        'src' => $url,
                        'type' => 'image',
                        'height' => 'auto',
                        'width' => 'auto'
                    ];
                }

            } catch (\Exception $e) {
                // If image processing fails, return error
                return response()->json([
                    'error' => 'Failed to process image: ' . $e->getMessage()
                ], 500);
            }
        }

        // Return in format expected by GrapesJS
        return response()->json([
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Determine the output extension based on input format
     */
    private function getOutputExtension(string $originalExtension): string
    {
        return in_array($originalExtension, ['png', 'webp']) ? $originalExtension : 'jpg';
    }

    /**
     * Encode image based on format with appropriate settings
     */
    private function encodeImage($image, string $originalExtension): string
    {
        switch (strtolower($originalExtension)) {
            case 'png':
                return $image->encode(new PngEncoder());

            case 'webp':
                return $image->encode(new WebpEncoder());

            default:
                // All other formats (jpeg, jpg, gif, etc.) go to JPG with 60% quality
                return $image->encode(new JpegEncoder(quality: 60));
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'src' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 422);
        }

        $src = $request->input('src');

        // Extract path from URL
        $path = str_replace('/storage/', '', parse_url($src, PHP_URL_PATH));

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}