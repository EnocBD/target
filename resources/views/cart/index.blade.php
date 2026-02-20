@extends('layouts.public')

@section('title', 'Carrito de Compras - Target Eyewear')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Carrito</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="fas fa-cart-shopping"></i> Carrito de Compras
        <span class="badge bg-primary fs-6 ms-2">{{ \LukePOLO\LaraCart\Facades\LaraCart::count() }} {{ \LukePOLO\LaraCart\Facades\LaraCart::count() == 1 ? 'producto' : 'productos' }}</span>
    </h1>

    @if(is_countable($items) && count($items) > 0)
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%">Producto</th>
                                    <th style="width: 15%">Precio</th>
                                    <th style="width: 20%">Cantidad</th>
                                    <th style="width: 15%" class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items-body">
                                @foreach($items as $item)
                                @php
                                    // Get product to retrieve image
                                    $productInCart = \App\Models\Product::find($item->id);
                                    $imagePath = $productInCart ? $productInCart->mainImage?->file_url : null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($imagePath)
                                            <img src="{{ $imagePath }}" alt="{{ $item->name }}" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                <i class="fas fa-image text-white fs-4"></i>
                                            </div>
                                            @endif
                                            <div>
                                                @if($item->options->slug ?? null)
                                                <a href="{{ route('products.show', $item->options->slug) }}" class="text-decoration-none">
                                                    <h6 class="mb-1">{{ $item->name }}</h6>
                                                </a>
                                                @else
                                                <h6 class="mb-1">{{ $item->name }}</h6>
                                                @endif
                                                @if($item->options->brand ?? null)
                                                <small class="text-muted"><i class="fas fa-tag"></i> {{ $item->options->brand }}</small><br>
                                                @endif
                                                @if($item->options->sku ?? null)
                                                <small class="text-muted">SKU: {{ $item->options->sku }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0 fw-bold">Gs. {{ number_format($item->price, 0, ',', '.') }}</p>
                                        @if($item->options->list_price ?? null && $item->options->list_price > $item->price)
                                        <small class="text-muted text-decoration-line-through">Gs. {{ number_format($item->options->list_price, 0, ',', '.') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" value="{{ $item->qty }}" min="1" max="99"
                                                   class="form-control form-control-sm cart-qty-input"
                                                   style="width: 70px;" data-item-id="{{ $item->getHash() }}" required>
                                            <button class="btn btn-sm btn-outline-primary cart-update-btn"
                                                    data-item-id="{{ $item->getHash() }}" title="Actualizar cantidad">
                                                <i class="fas fa-rotate"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0 fw-bold fs-5">Gs. {{ number_format($item->qty * $item->price, 0, ',', '.') }}</p>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger cart-remove-btn"
                                                data-item-id="{{ $item->getHash() }}" title="Eliminar del carrito">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3">
                <a href="{{ url('/productos') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Seguir Comprando
                </a>
                <form method="POST" action="{{ route('cart.clear') }}" class="d-inline clear-cart-form">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash-can"></i> Vaciar Carrito
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-receipt"></i> Resumen del Pedido
                    </h5>
                    <div id="cart-summary">
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
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
                        <span>Descuento:</span>
                        <strong>- Gs. {{ number_format($subTotal - ($total - $tax), 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5">Total:</span>
                        <strong class="fs-4 text-primary">Gs. {{ number_format($total, 0, ',', '.') }}</strong>
                    </div>
                    <a href="{{ route('cart.checkout') }}" class="btn btn-success w-100">
                        <i class="fab fa-whatsapp"></i> Proceder al Checkout
                    </a>
                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="fas fa-shield-halved"></i> Compra segura garantizada
                    </p>
                    </div>
                </div>
            </div>

            @if(auth()->check())
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-truck"></i> Información de Envío
                    </h6>
                    <p class="mb-0 small text-muted">
                        Los envíos se realizan dentro de las 24-48 horas hábiles. Te contactaremos por WhatsApp para coordinar la entrega.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-cart-shopping display-1 text-muted mb-3"></i>
        <h4>Tu carrito está vacío</h4>
        <p class="mb-4">Agrega productos para comenzar tu compra.</p>
        <a href="{{ url('/productos') }}" class="btn btn-primary">
            <i class="fas fa-store"></i> Ver Productos
        </a>
    </div>
    @endif
</div>
@endsection
