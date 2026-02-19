<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Process and store uploaded file for a model.
     */
    public function storeMedia(UploadedFile $file, Model $model, string $type = 'image', int $sortOrder = 0): Media
    {
        $mimeType = $file->getMimeType();
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        if ($type === 'image' && str_starts_with($mimeType, 'image/')) {
            return $this->processImage($file, $model, $originalName, $mimeType, $size, $sortOrder);
        }

        // Handle other media types (videos, documents)
        $path = $file->store('media', 'public');
        $url = Storage::url($path);

        return Media::create([
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'type' => $type,
            'file' => $url,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Process image file with resizing and thumbnail generation.
     * Solo genera redimensionados si la imagen es más grande que los tamaños objetivo.
     */
    protected function processImage(UploadedFile $file, Model $model, string $originalName, string $mimeType, int $size, int $sortOrder): Media
    {
        // Leer imagen para obtener dimensiones
        $image = $this->imageManager->read($file);
        $imageSize = $image->size();

        $width = $imageSize->width();
        $height = $imageSize->height();

        // Create directory structure
        $folderName = class_basename($model) . '/' . $model->id;
        $fileName = $originalName;

        // Tamaños objetivo
        $mainMaxWidth = 1080;
        $thumbnailMaxWidth = 576;

        // Determinar si hay que redimensionar
        $needsMainResize = $width > $mainMaxWidth;
        $needsThumbnailResize = $width > $thumbnailMaxWidth;

        // Procesar imagen principal solo si es más grande que el tamaño objetivo
        if ($needsMainResize) {
            $image->scaleDown($mainMaxWidth, null);
            $mainImagePath = "media/{$folderName}/{$fileName}";
            Storage::disk('public')->put($mainImagePath, $image->toJpeg(60));
            $mainImageUrl = Storage::url($mainImagePath);
        } else {
            // Usar imagen original ya que no necesita redimensión
            $path = $file->getRealPath();
            $mainImagePath = "media/{$folderName}/{$fileName}";
            Storage::disk('public')->put($mainImagePath, file_get_contents($path));
            $mainImageUrl = Storage::url($mainImagePath);
        }

        // Procesar thumbnail solo si es más grande que el tamaño objetivo
        if ($needsThumbnailResize) {
            $thumbnail = $this->imageManager->read($file);
            $thumbnail->scaleDown($thumbnailMaxWidth, null);
            $thumbnailPath = "media/{$folderName}/thumbnails/{$fileName}";
            Storage::disk('public')->put($thumbnailPath, $thumbnail->toJpeg(60));
            $thumbnailUrl = Storage::url($thumbnailPath);
        } else {
            // Si la imagen es pequeña, no generar thumbnail separado
            $thumbnailUrl = null;
        }

        return Media::create([
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'type' => 'image',
            'file' => $mainImageUrl,
            'thumbnail' => $thumbnailUrl,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Delete media and associated files.
     */
    public function deleteMedia(Media $media): bool
    {
        // Delete files from storage
        if ($media->file) {
            $filePath = str_replace('/storage/', '', $media->file);
            Storage::disk('public')->delete($filePath);
        }

        if ($media->thumbnail) {
            $thumbnailPath = str_replace('/storage/', '', $media->thumbnail);
            Storage::disk('public')->delete($thumbnailPath);
        }

        // Delete database record
        return $media->delete();
    }

    /**
     * Reorder media items.
     */
    public function reorderMedia(array $mediaIds): void
    {
        foreach ($mediaIds as $index => $mediaId) {
            Media::where('id', $mediaId)->update(['sort_order' => $index]);
        }
    }

    /**
     * Determine media type based on mime type.
     */
    protected function getMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'application/pdf') ||
            str_starts_with($mimeType, 'application/msword') ||
            str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument')) {
            return 'document';
        }

        return 'document'; // Default
    }
}
