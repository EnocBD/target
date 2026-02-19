{{-- resources/views/admin/pages/form.blade.php --}}
@extends('layouts.admin')

@section('title', $page->exists ? 'Editar Página' : 'Nueva Página')
@section('page-title', $page->exists ? 'Editar Página: ' . $page->title : 'Nueva Página')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span>
        <a href="{{ route('admin.pages.index') }}" class="text-primary hover:text-primary-700">Páginas</a>
    </li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">{{ $page->exists ? 'Editar' : 'Nueva' }}</span></li>
@endpush

@section('content')
    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-{{ $page->exists ? 'edit' : 'file-alt' }} mr-2 text-primary"></i>
        {{ $page->exists ? 'Editar Página: ' . $page->title : 'Crear nueva página' }}
    </h2>

    {{ html()->modelForm($page, $page->exists ? 'PUT' : 'POST', $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store'))->open() }}

    <div class="space-y-8">
        <!-- Información Básica -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="form-label">Título *</label>
                {{ html()->text('title')
                    ->class('form-input' . ($errors->has('title') ? ' border-red-500' : ''))
                    ->required()
                    ->placeholder('Ej: Sobre Nosotros, Productos, Contacto') }}
                @error('title')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="form-label">URL de la página *</label>
                {{ html()->text('slug')
                    ->class('form-input' . ($errors->has('slug') ? ' border-red-500' : ''))
                    ->required()
                    ->placeholder('contacto, sobre-nosotros') }}
                <p class="text-sm text-gray-600 mt-1">Use "/" para la página de inicio</p>
                @error('slug')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Estado y Menú -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label">Estado</label>
                <div class="space-y-2 bg-white p-4 rounded-lg border {{ $errors->has('is_active') ? 'border-red-300' : 'border-gray-200' }}">
                    {{ html()->hidden('is_active', 0) }}
                    <label for="is_active" class="flex items-center space-x-3 cursor-pointer">
                        {{ html()->checkbox('is_active', null, 1)
                            ->class('form-checkbox text-primary focus:ring-primary-500 focus:ring-2')
                            ->id('is_active') }}
                        <span class="text-gray-700 font-medium">Página Activa</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="menu_position" class="form-label">Posición en Menú</label>
                {{ html()->select('menu_position', [
                        '' => 'No mostrar en menú',
                        'main' => 'Menú Principal',
                        'footer_1' => 'Footer 1',
                        'footer_2' => 'Footer 2',
                    ], null)
                    ->class('form-input' . ($errors->has('menu_position') ? ' border-red-500' : '')) }}
                @error('menu_position')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- SEO y Página Padre -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="meta_title" class="form-label">Meta Título (SEO)</label>
                {{ html()->text('meta_title')
                    ->class('form-input' . ($errors->has('meta_title') ? ' border-red-500' : ''))
                    ->placeholder('Título para SEO (opcional)') }}
                @error('meta_title')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="parent_id" class="form-label">Página Padre</label>
                {{ html()->select('parent_id', ['' => 'Sin página padre'] + ($pages ?? []), null)
                    ->class('form-input' . ($errors->has('parent_id') ? ' border-red-500' : '')) }}
                @error('parent_id')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Meta Descripción -->
        <div>
            <label for="meta_description" class="form-label">Meta Descripción (SEO)</label>
            {{ html()->textarea('meta_description')
                ->class('form-input' . ($errors->has('meta_description') ? ' border-red-500' : ''))
                ->rows(3)
                ->placeholder('Descripción para resultados de búsqueda (150-160 caracteres recomendados)') }}
            @error('meta_description')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contenido HTML -->
        <div>
            <label for="content" class="form-label">Contenido HTML (Opcional)</label>
            {{ html()->textarea('content')
                ->class('form-input font-mono text-sm' . ($errors->has('content') ? ' border-red-500' : ''))
                ->rows(8)
                ->placeholder('Contenido HTML opcional - normalmente se usan bloques de contenido') }}
            <p class="text-sm text-gray-600 mt-1">💡 Recomendación: Usar bloques de contenido para mayor flexibilidad</p>
            @error('content')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Volver a la lista
            </a>
            {{ html()->submit($page->exists ? '<i class="fas fa-save mr-2"></i> Actualizar Página' : '<i class="fas fa-save mr-2"></i> Crear Página')
                ->class('btn btn-primary') }}
        </div>
    </div>

    {{ html()->closeModelForm() }}
@endsection