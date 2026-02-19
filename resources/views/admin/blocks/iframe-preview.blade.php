@extends('layouts.preview')

@section('title', 'Vista Previa del Bloque')

@section('content')
<div style="min-height: 100vh; padding: 0;">
    @if($block && $block->block_type)
        @php
            $blockTemplate = 'blocks.' . $block->block_type;
            $templateExists = view()->exists($blockTemplate);
        @endphp

        @if($templateExists)
            @php
                // Si existe data_preview, usarlo, sino usar data
                $blockData = $block->data_preview ?? $block->data;
                $dataToUse = is_object($blockData) ? json_decode(json_encode($blockData), true) :
                             (is_array($blockData) ? $blockData : []);
            @endphp
            @include($blockTemplate, ['block' => $block, 'data' => $dataToUse, 'text' => $dataToUse])
        @else
            <div style="padding: 40px; text-align: center; background: #f8f9fa; color: #6c757d;">
                <h3>Vista previa no disponible</h3>
                <p>No se encontró el template para el tipo de bloque: <strong>{{ $block->block_type }}</strong></p>
                <small>Buscando: {{ $blockTemplate }}</small>
            </div>
        @endif
    @else
        <div style="padding: 80px; text-align: center; background: #f8f9fa; color: #6c757d;">
            <div style="max-width: 400px; margin: 0 auto;">
                <i class="fas fa-eye" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3 style="margin-bottom: 16px;">Vista Previa</h3>
                <p style="margin-bottom: 20px;">Selecciona un tipo de bloque para ver la vista previa</p>
                <div style="padding: 20px; background: white; border-radius: 8px; border: 2px dashed #dee2e6;">
                    <small style="color: #adb5bd;">El bloque aparecerá aquí con el diseño del sitio</small>
                </div>
            </div>
        </div>
    @endif
</div>
