@props([
    'product' => null,
    'showAddToCart' => true,
])

@if($product)
    <div class="card h-100 border-0 shadow-sm hover-lift">
        {{-- Product Image --}}
        <a href="{{ route('products.show', $product->slug) }}">
            <div class="ratio ratio-1x1">
                @if($product->mainImage)
                    <img src="{{ asset($product->mainImage->thumbnail) }}"
                         alt="{{ $product->name }}" class="object-fit-cover">
                @else
                    <div class="bg-secondary d-flex align-items-center justify-content-center">
                        <i class="fas fa-image display-4 text-white"></i>
                    </div>
                @endif
            </div>
        </a>

        <div class="card-body d-flex flex-column">
            {{-- Brand --}}
            @if($product->brand)
                <small class="text-muted text-uppercase">{{ $product->brand->name }}</small>
            @endif

            {{-- Product Name --}}
            <h5 class="card-title fw-bold">
                <a href="{{ route('products.show', $product->slug) }}"
                   class="text-decoration-none text-dark">
                    {{ $product->name }}
                </a>
            </h5>

            <div class="mt-auto">
                {{-- List Price (if greater than price) --}}
                @if($product->list_price && $product->list_price > $product->price)
                    <p class="card-text text-muted text-decoration-line-through small mb-1">
                        Gs. {{ number_format($product->list_price, 0, ',', '.') }}
                    </p>
                @endif

                {{-- Current Price --}}
                <p class="card-text fs-5 fw-bold text-primary mb-2">
                    Gs. {{ number_format($product->price, 0, ',', '.') }}
                </p>

                {{-- Action Buttons --}}
                @if($showAddToCart)
                    <div class="d-flex gap-2">
                        <a href="{{ route('products.show', $product->slug) }}"
                           class="btn btn-outline-primary btn-sm flex-grow-1">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1 add-to-cart-form">
                            @csrf
                            <input type="hidden" name="qty" value="1">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('products.show', $product->slug) }}"
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
