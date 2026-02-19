<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-settings')->only(['index']);
        $this->middleware('permission:edit-settings')->only(['update']);

        // Inicializar ImageManager con driver GD
        $this->imageManager = new ImageManager(new Driver());
    }

    public function index()
    {
        $settings = Setting::getAllGrouped();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Validación base
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp,svg|max:102400', // 100MB max
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'nullable|string'
        ]);

        // Procesar eliminaciones de imágenes PRIMERO (antes de subir nuevas)
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $key => $shouldDelete) {
                if ($shouldDelete === '1') {
                    $this->deleteImageSetting($key);
                }
            }
        }

        // Procesar configuraciones de texto normales
        if (isset($validated['settings'])) {
            foreach ($validated['settings'] as $key => $value) {
                $setting = Setting::where('key', $key)->first();

                // Solo actualizar si no es de tipo image o si la imagen no fue eliminada
                if (!$setting || $setting->type !== 'image') {
                    Setting::set($key, $value);
                }
            }
        }

        // Procesar imágenes subidas
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $file) {
                if ($file && $file->isValid()) {
                    $this->processImageSetting($key, $file);
                }
            }
        }

        // Limpiar cache de settings
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Procesar y guardar imagen de configuración
     */
    protected function processImageSetting($key, $file)
    {
        try {
            // Generar nombre único para el archivo
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'settings/' . $key . '_' . time() . '_' . Str::random(10) . '.' . $extension;

            // Si es SVG, guardar directamente sin procesamiento
            if ($extension === 'svg') {
                // Guardar el archivo SVG directamente
                Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));
            } else {
                // Procesar imágenes normales (PNG, JPG, WebP, etc.)
                // Leer la imagen
                $image = $this->imageManager->read($file->getRealPath());

                // Obtener dimensiones originales
                $originalWidth = $image->width();
                $originalHeight = $image->height();

                // Redimensionar manteniendo proporción si es mayor a 1920px de ancho
                if ($originalWidth > 1920) {
                    $image->scale(width: 1920);
                }

                // Codificar la imagen manteniendo transparencia
                switch ($extension) {
                    case 'png':
                    case 'gif':
                        // Mantener transparencia para PNG y GIF
                        $encodedImage = $image->toPng();
                        $filename = str_replace('.' . $extension, '.png', $filename);
                        break;
                    case 'webp':
                        // WebP soporta transparencia
                        $encodedImage = $image->toWebp(quality: 90);
                        break;
                    default:
                        // JPEG no soporta transparencia, convertir a PNG si había transparencia
                        if ($this->hasTransparency($file)) {
                            $encodedImage = $image->toPng();
                            $filename = str_replace('.' . $extension, '.png', $filename);
                        } else {
                            $encodedImage = $image->toJpeg(quality: 90);
                        }
                        break;
                }

                // Guardar imagen procesada en storage
                Storage::disk('public')->put($filename, $encodedImage);
            }

            // Eliminar imagen anterior si existe
            $oldSetting = Setting::where('key', $key)->first();
            if ($oldSetting && $oldSetting->value) {
                $oldPath = str_replace(Storage::url(''), '', $oldSetting->value);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Guardar URL en la base de datos
            $imageUrl = Storage::url($filename);
            Setting::set($key, $imageUrl, null, 'image');

        } catch (\Exception $e) {
            \Log::error('Error procesando imagen de configuración: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar imagen de configuración
     */
    protected function deleteImageSetting($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if ($setting && $setting->value) {
                // Eliminar archivo físico del storage
                $oldPath = str_replace(Storage::url(''), '', $setting->value);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                // Limpiar valor en la base de datos (establecer como null o string vacío)
                $setting->value = null;
                $setting->save();
            }
        } catch (\Exception $e) {
            \Log::error('Error eliminando imagen de configuración: ' . $e->getMessage());
            // No lanzar excepción para no interrumpir el proceso
        }
    }

    /**
     * Verificar si una imagen tiene transparencia
     */
    protected function hasTransparency($file)
    {
        try {
            $image = $this->imageManager->read($file->getRealPath());

            // Para PNG, verificar si tiene canal alfa
            if ($file->getClientOriginalExtension() === 'png') {
                return true; // Asumimos que PNG puede tener transparencia
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}