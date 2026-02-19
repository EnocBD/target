{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Usuarios</span></li>
@endpush

@section('content')
    <div x-data="{ showDeleteModal: false, userId: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
                <p class="text-gray-600 mt-1">Administra los usuarios del sistema</p>
            </div>
            @can('create-users')
                <a href="{{ route('admin.users.create') }}"
                   class="btn btn-primary group shadow-lg">
                    <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                    Nuevo Usuario
                </a>
            @endcan
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($users->count() > 0)
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Roles</th>
                            <th>Fecha Registro</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-primary to-primary-700 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                                @if($user->id === auth()->id())
                                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Tú
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">Miembro desde {{ $user->created_at->format('M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-gray-900">{{ $user->email }}</div>
                                </td>
                                <td class="text-center">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 text-sm">Sin roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="text-gray-900">{{ $user->created_at->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        @can('edit-users')
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="action-btn action-btn-edit"
                                               title="Editar usuario">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endcan

                                        @can('delete-users')
                                            @if($user->id !== auth()->id())
                                                <button type="button"
                                                        class="action-btn action-btn-delete"
                                                        title="Eliminar usuario"
                                                        @click="userId = {{ $user->id }}; showDeleteModal = true">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Mostrando {{ $users->firstItem() }}-{{ $users->lastItem() }} de {{ $users->total() }} usuarios
                    </div>
                    <div class="pagination-nav">
                        {{ $users->links() }}
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-user-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="empty-state-title">No hay usuarios registrados</h3>
                    <p class="empty-state-description">
                        Comienza añadiendo usuarios al sistema. Los usuarios pueden tener diferentes roles y permisos.
                    </p>
                    @can('create-users')
                        <a href="{{ route('admin.users.create') }}"
                           class="btn btn-primary group shadow-lg">
                            <i class="fas fa-plus mr-2 group-hover:scale-110 transition-transform"></i>
                            Crear Primer Usuario
                        </a>
                    @endcan
                </div>
            @endif
        </div>

        @can('delete-users')
            <!-- Delete Modal -->
            <div x-show="showDeleteModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-cloak>
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                         @click="showDeleteModal = false"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div x-show="showDeleteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg leading-6 font-semibold text-gray-900">
                                        Confirmar eliminación
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">
                                            ¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer y se perderán todos los datos asociados.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button @click="showDeleteModal = false"
                                    class="btn btn-secondary w-full sm:w-auto">
                                Cancelar
                            </button>
                            <form :action="'{{ route('admin.users.destroy', ':id') }}'.replace(':id', userId)"
                                  method="POST" class="w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-danger w-full sm:w-auto">
                                    <i class="fas fa-trash mr-2"></i>
                                    Eliminar usuario
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection
