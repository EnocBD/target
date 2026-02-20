@extends('layouts.admin')

@section('title', $brand->exists ? 'Editar Marca' : 'Nueva Marca')
@section('page-title', $brand->exists ? 'Editar Marca: ' . $brand->name : 'Nueva Marca')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span>
        <a href="{{ route('admin.brands.index') }}" class="text-primary hover:text-primary-700">Marcas</a>
    </li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">{{ $brand->exists ? 'Editar' : 'Nueva' }}</span></li>
@endpush

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-trademark mr-2 text-primary"></i>
            {{ $brand->exists ? 'Editar Marca: ' . $brand->name : 'Crear nueva Marca' }}
        </h2>

        <form action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}"
              method="POST"
              class="space-y-8">
            @csrf
            @if($brand->exists)
                @method('PUT')
            @endif

            <!-- Información Básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label">Nombre de la Marca *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $brand->name) }}"
                           class="form-input {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           required placeholder="Ingrese el nombre de la marca">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="form-label">Orden</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}"
                           class="form-input" placeholder="0" min="0">
                    @error('sort_order')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="form-label">Descripción</label>
                <textarea id="description" name="description" rows="4" class="form-input"
                          placeholder="Descripción de la marca">{{ old('description', $brand->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}
                           class="form-checkbox h-5 w-5 text-primary border-gray-300 rounded focus:ring-primary">
                    <span class="ml-2 text-gray-900">Marca Activa</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Las marcas inactivas no se mostrarán en el sitio.</p>
            </div>

            <!-- SEO Fields -->
            <div class="pt-6 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-900 mb-4">SEO y Metadatos</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="form-label">Meta Título</label>
                        <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $brand->meta_title) }}"
                               class="form-input" placeholder="Título para SEO (60 caracteres recomendado)">
                        @error('meta_title')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="form-label">Meta Descripción</label>
                        <input type="text" id="meta_description" name="meta_description" value="{{ old('meta_description', $brand->meta_description) }}"
                               class="form-input" placeholder="Descripción para SEO (160 caracteres recomendado)">
                        @error('meta_description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    {{ $brand->exists ? 'Actualizar' : 'Crear' }} Marca
                </button>
            </div>
        </form>
    </div>
@endsection
