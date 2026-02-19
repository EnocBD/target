@extends('layouts.public')

@section('title', 'Checkout - Target Eyewear')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Carrito</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="fas fa-clipboard-check"></i> Finalizar Compra
    </h1>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('cart.process') }}">
                @csrf

                <!-- Datos Personales -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Datos Personales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" required value="{{ old('name') }}">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Teléfono / WhatsApp *</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" required value="{{ old('phone') }}" placeholder="+595 9XX XXX XXX">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Te contactaremos por WhatsApp para confirmar tu pedido</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dirección de Entrega -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-location-dot"></i> Dirección de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección Completa *</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3" required placeholder="Calle, número, referencias...">{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas Adicionales (Opcional)</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Horarios de entrega, puntos de referencia, etc.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Método de Entrega -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-truck"></i> Método de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Envío a domicilio:</strong> Los envíos se realizan dentro de las 24-48 horas hábiles. El costo del envío se coordina al momento de confirmar el pedido por WhatsApp.
                        </div>
                    </div>
                </div>

                <!-- Botón de Confirmar -->
                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fab fa-whatsapp"></i> Confirmar Pedido por WhatsApp
                </button>
                <p class="text-center text-muted small mt-3 mb-0">
                    Al confirmar, serás redirigido a WhatsApp con los detalles de tu pedido.
                </p>
            </form>
        </div>

        <!-- Resumen del Pedido -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        @foreach($items as $item)
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $item->name }}</h6>
                                <small class="text-muted">Cantidad: {{ $item->qty }}</small>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 fw-bold">Gs. {{ number_format($item->qty * $item->price, 0, ',', '.') }}</p>
                                @if($item->options->list_price ?? null && $item->options->list_price > $item->price)
                                <small class="text-muted text-decoration-line-through">Gs. {{ number_format($item->qty * $item->options->list_price, 0, ',', '.') }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ count($items) }} {{ count($items) == 1 ? 'producto' : 'productos' }}):</span>
                        <strong>Gs. {{ number_format($subTotal, 0, ',', '.') }}</strong>
                    </div>

                    @if($tax > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>IVA (10%):</span>
                        <strong>Gs. {{ number_format($tax, 0, ',', '.') }}</strong>
                    </div>
                    @endif

                    @if($subTotal != $total)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Ahorras:</span>
                        <strong>- Gs. {{ number_format($subTotal - ($total - $tax), 0, ',', '.') }}</strong>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5">Total:</span>
                        <strong class="fs-4 text-primary">Gs. {{ number_format($total, 0, ',', '.') }}</strong>
                    </div>

                    <div class="alert alert-success small mb-0">
                        <i class="fas fa-shield-halved"></i>
                        <strong>Compra Segura:</strong> Tus datos están protegidos
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
