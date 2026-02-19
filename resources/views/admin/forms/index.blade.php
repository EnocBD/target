@extends('layouts.admin')

@section('title', 'Formularios')
@section('page-title', 'Formularios')

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">Formularios</span></li>
@endpush

@section('content')
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Formularios Enviados</h1>
            <p class="text-gray-600 mt-1">Gestiona los formularios recibidos desde el sitio web</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <form method="GET" action="{{ route('admin.forms.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="contacto" {{ request('type') === 'contacto' ? 'selected' : '' }}>Contacto</option>
                    <option value="trabaja-con-nosotros" {{ request('type') === 'trabaja-con-nosotros' ? 'selected' : '' }}>Trabaja con Nosotros</option>
                </select>
            </div>
            <div>
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Nuevo</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Leído</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archivado</option>
                </select>
            </div>
            <div>
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-input" placeholder="Nombre o email..." value="{{ request('search') }}">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Información</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($submissions as $submission)
                        <tr class="{{ $submission->status === 'new' ? 'bg-yellow-50' : '' }} hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $submission->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $submission->form_type === 'contacto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $submission->form_type === 'contacto' ? 'Contacto' : 'Trabaja con Nosotros' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($submission->status)
                                    @case('new')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <span class="w-2 h-2 mr-1.5 bg-yellow-400 rounded-full"></span>
                                            Nuevo
                                        </span>
                                        @break
                                    @case('read')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <span class="w-2 h-2 mr-1.5 bg-blue-400 rounded-full"></span>
                                            Leído
                                        </span>
                                        @break
                                    @case('archived')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <span class="w-2 h-2 mr-1.5 bg-gray-400 rounded-full"></span>
                                            Archivado
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $email = collect($submission->form_data)->firstWhere('name', 'email')['value'] ?? '';
                                    $nombre = collect($submission->form_data)->firstWhere('name', 'nombre')['value'] ?? 
                                             collect($submission->form_data)->firstWhere('name', 'nombre_completo')['value'] ?? '';
                                @endphp
                                <div class="text-sm font-medium text-gray-900">{{ $nombre }}</div>
                                <div class="text-sm text-gray-500">{{ $email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('admin.forms.show', $submission) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Ver
                                </a>
                                @if($submission->status === 'new')
                                    <form method="POST" action="{{ route('admin.forms.mark-read', $submission) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">
                                            Marcar Leído
                                        </button>
                                    </form>
                                @endif
                                @if($submission->status !== 'archived')
                                    <form method="POST" action="{{ route('admin.forms.archive', $submission) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                            Archivar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay formularios enviados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $submissions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection