@extends('layouts.admin')

@section('title', 'Formulario #' . $submission->id)
@section('page-title', 'Formulario #' . $submission->id)

@push('breadcrumbs')
    <li><span class="text-gray-500"> / </span><a href="{{ route('admin.forms.index') }}" class="text-gray-700 hover:text-gray-900">Formularios</a></li>
    <li><span class="text-gray-500"> / </span><span class="text-gray-700">#{{ $submission->id }}</span></li>
@endpush

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Form Details Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Formulario {{ $submission->form_type === 'contacto' ? 'de Contacto' : 'Trabaja con Nosotros' }}
                    </h2>
                    @switch($submission->status)
                        @case('new')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <span class="w-2 h-2 mr-2 bg-yellow-400 rounded-full"></span>
                                Nuevo
                            </span>
                            @break
                        @case('read')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <span class="w-2 h-2 mr-2 bg-blue-400 rounded-full"></span>
                                Leído
                            </span>
                            @break
                        @case('archived')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <span class="w-2 h-2 mr-2 bg-gray-400 rounded-full"></span>
                                Archivado
                            </span>
                            @break
                    @endswitch
                </div>

                <!-- Metadata -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                        <dd class="text-sm text-gray-900">{{ $submission->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dirección IP</dt>
                        <dd class="text-sm text-gray-900">{{ $submission->ip_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                        <dd class="text-sm text-gray-900 truncate" title="{{ $submission->user_agent }}">{{ $submission->user_agent }}</dd>
                    </div>
                </div>

                <!-- Form Data -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Formulario</h3>
                    @if($submission->form_data)
                        <div class="space-y-4">
                            @foreach($submission->form_data as $field)
                                <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                    <dt class="text-sm font-medium text-gray-500 mb-1">
                                        {{ $field['label'] ?? $field['name'] }}
                                    </dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($field['type'] === 'textarea')
                                            <div class="whitespace-pre-wrap bg-gray-50 p-3 rounded-md">{{ $field['value'] }}</div>
                                        @else
                                            {{ $field['value'] }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Uploaded Files -->
                @if($submission->uploaded_files && count($submission->uploaded_files) > 0)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Archivos Adjuntos</h3>
                        <div class="space-y-3">
                            @foreach($submission->uploaded_files as $file)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-lines text-2xl text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $file['label'] ?? $file['name'] }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $file['original_name'] }} • {{ $file['mime_type'] }} • {{ number_format($file['size'] / 1024, 2) }} KB
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" 
                                       class="btn btn-outline text-sm">
                                        <i class="fas fa-download mr-2"></i>
                                        Descargar
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                <div class="space-y-3">
                    @if($submission->status === 'new')
                        <form method="POST" action="{{ route('admin.forms.mark-read', $submission) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-circle-check mr-2"></i>
                                Marcar como Leído
                            </button>
                        </form>
                    @endif

                    @if($submission->status !== 'archived')
                        <form method="POST" action="{{ route('admin.forms.archive', $submission) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline w-full">
                                <i class="fas fa-archive mr-2"></i>
                                Archivar
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.forms.destroy', $submission) }}" 
                          onsubmit="return confirm('¿Está seguro de eliminar este formulario?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar
                        </button>
                    </form>

                    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline w-full">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Listado
                    </a>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notas Internas</h3>
                <form method="POST" action="{{ route('admin.forms.update-notes', $submission) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <textarea name="notes" rows="5" class="form-input" 
                                 placeholder="Agregar notas internas...">{{ $submission->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Notas
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection