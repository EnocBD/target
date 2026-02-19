@extends('layouts.admin')

@section('title', $user->exists ? 'Editar Usuario' : 'Nuevo usuario')
@section('page-title', $user->exists ? 'Editar Usuario: ' . $user->name : 'Nuevo usuario')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span>
        <a href="{{ route('admin.users.index') }}" class="text-primary hover:text-primary-700">Usuarios</a>
    </li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">{{ $user->exists ? 'Editar' : 'Nuevo' }}</span></li>
@endpush

@section('content')
    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-user-edit mr-2 text-primary"></i>
        {{ $user->exists ? 'Editar Usuario: ' . $user->name : 'Crear nuevo usuario' }}
    </h2>

    {{ html()->modelForm($user, $user->exists ? 'PUT' : 'POST', $user->exists ? route('admin.users.update', $user) : route('admin.users.store'))->open() }}

    <div class="space-y-8">
        <!-- Nombre y Email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="form-label">Nombre Completo *</label>
                {{ html()->text('name')
                    ->class('form-input' . ($errors->has('name') ? ' border-red-500' : ''))
                    ->required()
                    ->placeholder('Ingrese el nombre completo') }}
                @error('name')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email *</label>
                {{ html()->email('email')
                    ->class('form-input' . ($errors->has('email') ? ' border-red-500' : ''))
                    ->required()
                    ->placeholder('usuario@ejemplo.com') }}
                @error('email')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Contraseña -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="form-label">
                    {{ $user->exists ? 'Nueva Contraseña' : 'Contraseña *' }}
                </label>
                {{ html()->password('password')
                    ->class('form-input' . ($errors->has('password') ? ' border-red-500' : ''))
                    ->placeholder($user->exists ? 'Dejar en blanco para mantener la actual' : 'Mínimo 8 caracteres')
                    ->when(!$user->exists, fn($input) => $input->required()) }}
                @if($user->exists)
                    <p class="text-sm text-gray-600 mt-1">Dejar en blanco si no deseas cambiar la contraseña</p>
                @endif
                @error('password')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">
                    {{ $user->exists ? 'Confirmar Nueva Contraseña' : 'Confirmar Contraseña *' }}
                </label>
                {{ html()->password('password_confirmation')
                    ->class('form-input')
                    ->placeholder('Confirmar contraseña') }}
            </div>
        </div>

        <!-- Estado de cuenta -->
        <div>
            <label for="is_active" class="flex items-center space-x-3 cursor-pointer">
                {{ html()->checkbox(
                    'is_active',
                    old('is_active', $user->is_active ?? false),
                    '1'
                )
                ->class('form-checkbox text-primary focus:ring-primary-500 focus:ring-2')
                ->id('is_active') }}
                <div>
                    <span class="text-gray-700 font-medium">Cuenta Activa</span>
                    <p class="text-sm text-gray-600">Los usuarios con cuentas inactivas no podrán acceder al sitio cuando BLOCK_SITE=1</p>
                </div>
            </label>
            @error('is_active')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Roles -->
        <div>
            <label class="form-label">Roles *</label>
            <div class="space-y-2 bg-white p-4 rounded-lg border {{ $errors->has('roles') ? 'border-red-300' : 'border-gray-200' }}">
                @foreach($roles as $role)
                    <label for="role_{{ $role->id }}" class="flex items-center space-x-3 cursor-pointer">
                        {{ html()->checkbox(
                            'roles[]',
                            $user->exists ? $user->hasRole($role->name) : in_array($role->name, old('roles', [])),
                            $role->name
                        )
                        ->class('form-checkbox text-primary focus:ring-primary-500 focus:ring-2')
                        ->id('role_' . $role->id) }}
                        <span class="text-gray-700 font-medium">{{ ucfirst($role->name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('roles')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
            {{ html()->submit($user->exists ? '<i class="fas fa-save mr-2"></i> Actualizar Usuario' : '<i class="fas fa-save mr-2"></i> Crear Usuario')
                ->class('btn btn-primary') }}
        </div>
    </div>

    {{ html()->closeModelForm() }}
@endsection