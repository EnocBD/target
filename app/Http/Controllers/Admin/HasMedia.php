<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Project;
use App\Services\MediaService;
use Illuminate\Http\Request;

trait HasMedia
{
    protected MediaService $mediaService;

    // ========== PRODUCT MEDIA METHODS ==========

    /**
     * Upload media for product
     */
    public function uploadProductMedia(Request $request, Product $product)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:image,video,document'
        ]);

        try {
            $media = $this->mediaService->storeMedia(
                $request->file('file'),
                $product,
                $request->type,
                $product->media()->count()
            );

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'type' => $media->type,
                    'file_url' => $media->file_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'original_name' => $media->original_name,
                    'size' => $media->size
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete media from product
     */
    public function deleteProductMedia(Product $product, $mediaId)
    {
        try {
            $media = $product->media()->findOrFail($mediaId);
            $this->mediaService->deleteMedia($media);

            return response()->json([
                'success' => true,
                'message' => 'Media eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reorder media for product
     */
    public function reorderProductMedia(Request $request, Product $product)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer'
        ]);

        try {
            $this->mediaService->reorderMedia($request->media_ids);

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ========== GENERIC MEDIA METHODS (works for any entity) ==========

    /**
     * Upload media for any entity using morph
     * Este método acepta el modelo directamente (inyectado por Laravel)
     */
    public function uploadMedia(Request $request, $model = null)
    {
        // Si $model es null o un string, intentamos resolverlo desde los parámetros de ruta
        if ($model === null || is_string($model)) {
            $entityType = request()->route()->parameterNames[0] ?? null; // 'category' o 'product'
            $entityId = request()->route()->parameters[$entityType] ?? null;

            if ($entityType && $entityId) {
                $model = $this->resolveModel($entityType, $entityId);
            }
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:image,video,document'
        ]);

        try {
            $media = $this->mediaService->storeMedia(
                $request->file('file'),
                $model,
                $request->type,
                $model->media()->count()
            );

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'type' => $media->type,
                    'file_url' => $media->file_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'original_name' => $media->original_name,
                    'size' => $media->size
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete media from any entity
     */
    public function deleteMedia(Request $request, $model = null, $mediaId = null)
    {
        // Si $model es null o un string, intentamos resolverlo
        if ($model === null || is_string($model)) {
            $entityType = request()->route()->parameterNames[0] ?? null;
            $entityId = request()->route()->parameters[$entityType] ?? null;

            if ($entityType && $entityId) {
                $model = $this->resolveModel($entityType, $entityId);
            }
        }

        // Si mediaId es null, intentar obtenerlo de la ruta
        if ($mediaId === null) {
            $mediaId = request()->route()->parameter('mediaId');
        }

        try {
            $media = $model->media()->findOrFail($mediaId);
            $this->mediaService->deleteMedia($media);

            return response()->json([
                'success' => true,
                'message' => 'Media eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reorder media for any entity
     */
    public function reorderMedia(Request $request, $model = null)
    {
        // Si $model es null o un string, intentamos resolverlo
        if ($model === null || is_string($model)) {
            $entityType = request()->route()->parameterNames[0] ?? null;
            $entityId = request()->route()->parameters[$entityType] ?? null;

            if ($entityType && $entityId) {
                $model = $this->resolveModel($entityType, $entityId);
            }
        }

        $request->validate([
            'media' => 'required|array',
            'media.*.id' => 'required|exists:media,id',
            'media.*.sort_order' => 'required|integer'
        ]);

        try {
            foreach ($request->media as $item) {
                $model->media()->where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Resolve model from route parameters
     */
    protected function resolveModel($entityType, $entityId)
    {
        $entityMap = [
            'product' => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
            'brand' => \App\Models\Brand::class,
            'project' => \App\Models\Project::class,
        ];

        // El parámetro de ruta ya es singular (category, product, etc.)
        if (!isset($entityMap[$entityType])) {
            throw new \Exception("Entidad no válida: {$entityType}");
        }

        $modelClass = $entityMap[$entityType];
        return $modelClass::findOrFail($entityId);
    }

    // ========== PROJECT MEDIA METHODS ==========

    /**
     * Upload media for project
     */
    public function uploadProjectMedia(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:image,video,document'
        ]);

        try {
            $media = $this->mediaService->storeMedia(
                $request->file('file'),
                $project,
                $request->type,
                $project->media()->count()
            );

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'type' => $media->type,
                    'file_url' => $media->file_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'original_name' => $media->original_name,
                    'size' => $media->size
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete media from project
     */
    public function deleteProjectMedia(Project $project, $mediaId)
    {
        try {
            $media = $project->media()->findOrFail($mediaId);
            $this->mediaService->deleteMedia($media);

            return response()->json([
                'success' => true,
                'message' => 'Media eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reorder media for project
     */
    public function reorderProjectMedia(Request $request, Project $project)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer'
        ]);

        try {
            $this->mediaService->reorderMedia($request->media_ids);

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}