@extends('layouts.public')

@section('title', $product->name . ' - Target Eyewear')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/productos') }}">Productos</a></li>
            @if($product->category)
            <li class="breadcrumb-item"><a href="{{ url('/productos?categories[]=' . $product->category->slug) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            @if($product->images && $product->images->count() > 0)
                <!-- Main Image -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-0">
                        <div class="swiper product-main-swiper">
                            <div class="swiper-wrapper">
                                @foreach($product->images as $image)
                                <div class="swiper-slide">
                                    <a href="{{ asset($image->file) }}"
                                       class="main-image-popup"
                                       title="{{ $product->name }}"
                                       data-index="{{ $loop->index }}">
                                        <img src="{{ asset($image->file) }}"
                                             alt="{{ $product->name }}"
                                             class="img-fluid w-100"
                                             style="object-fit: cover; height: 500px; cursor: zoom-in;">
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            <!-- Navigation buttons -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>

                <!-- Thumbnail Gallery (Grid System) -->
                @if($product->images->count() > 1)
                <div class="row g-3">
                    @foreach($product->images as $image)
                    <div class="col-3">
                        <div class="card border-0 thumbnail-card" data-slide-index="{{ $loop->index }}">
                            <img src="{{ asset($image->thumbnail) }}"
                                 alt="{{ $image->title ?? $product->name }}"
                                 class="img-fluid"
                                 style="height: 100px; object-fit: cover; width: 100%; cursor: pointer; opacity: 0.6; transition: opacity 0.3s;">
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            @else
                <!-- No image placeholder -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 500px;">
                            <i class="fas fa-image display-1 text-muted"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    @if($product->brand)
                    <p class="text-muted text-uppercase mb-2">{{ $product->brand->name }}</p>
                    @endif

                    <h1 class="h2 fw-bold mb-3">{{ $product->name }}</h1>

                    @if($product->sku)
                    <p class="text-muted mb-3">SKU: {{ $product->sku }}</p>
                    @endif

                    <!-- Price -->
                    <div class="mb-4">
                        @if($product->list_price && $product->list_price > $product->price)
                        <p class="text-muted text-decoration-line-through fs-5 mb-1">
                            Gs. {{ number_format($product->list_price, 0, ',', '.') }}
                        </p>
                        @endif
                        <p class="fs-2 fw-bold text-primary mb-0">
                            Gs. {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                        @if($product->list_price && $product->list_price > $product->price)
                        <span class="badge bg-success">
                            Ahorras Gs. {{ number_format($product->list_price - $product->price, 0, ',', '.') }}
                        </span>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($product->text || $product->description)
                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">Descripción</h5>
                        <p class="text-muted">
                            {!! $product->text ?? $product->description !!}
                        </p>
                    </div>
                    @endif

                    <!-- Category -->
                    @if($product->category)
                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">Categoría</h5>
                        <a href="{{ url('/productos?categories[]=' . $product->category->slug) }}"
                           class="btn btn-outline-primary btn-sm">
                            {{ $product->category->name }}
                        </a>
                    </div>
                    @endif

                    <hr>

                    <!-- Add to Cart Form -->
                    <form method="POST" action="{{ route('cart.add') }}" class="row g-3 align-items-end add-to-cart-form">
                        @csrf
                        <div class="col-auto">
                            <label for="qty" class="form-label fw-bold">Cantidad</label>
                            <input type="number"
                                   name="qty"
                                   id="qty"
                                   value="1"
                                   min="1"
                                   max="99"
                                   class="form-control form-control-lg"
                                   style="width: 100px;"
                                   required>
                        </div>
                        <div class="col">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-cart-plus"></i> Agregar al Carrito
                            </button>
                        </div>
                    </form>

                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-shield-halved"></i>
                        Envíos seguros a todo el país
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    @if($product->description)
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                                <i class="fas fa-file-lines"></i> Descripción
                            </button>
                        </li>
                        @if($product->brand)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand" type="button" role="tab">
                                <i class="fas fa-tag"></i> Marca
                            </button>
                        </li>
                        @endif
                    </ul>
                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <div class="pt-3">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                        @if($product->brand)
                        <div class="tab-pane fade" id="brand" role="tabpanel">
                            <div class="pt-3">
                                <h5>{{ $product->brand->name }}</h5>
                                <p class="text-muted">Productos de alta calidad de {{ $product->brand->name }}</p>
                                <a href="{{ url('/productos?brands[]=' . $product->brand->slug) }}"
                                   class="btn btn-outline-primary">
                                    Ver más productos de {{ $product->brand->name }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Similar Products -->
    @if($similarProducts && $similarProducts->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4">
                <i class="fas fa-star"></i> Productos Similares
            </h3>
        </div>
    </div>
    <div class="row g-4 mb-5">
        @foreach($similarProducts as $similar)
        <div class="col-lg-3 col-md-6">
            <x-product-card :product="$similar" :showAddToCart="true" />
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('styles')
<style>
/* Thumbnail styles */
.thumbnail-card img {
    opacity: 0.6;
    transition: all 0.3s;
}

.thumbnail-card.active img {
    opacity: 1;
    border: 2px solid #0d6efd;
    border-radius: 4px;
}

.thumbnail-card:hover img {
    opacity: 0.8;
}

/* Magnific Popup fade animation */
.mfp-with-fade {
    position: relative;
}
.mfp-with-fade .mfp-content {
    opacity: 0;
    transition: opacity 0.3s ease;
}
.mfp-with-fade.mfp-ready .mfp-content {
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($product->images && $product->images->count() > 0)
    var mainSwiper = new Swiper('.product-main-swiper', {
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        on: {
            slideChange: function() {
                updateThumbnailActive(this.activeIndex);
            }
        }
    });

    // Initialize first thumbnail as active
    var firstThumbnail = document.querySelector('.thumbnail-card[data-slide-index="0"]');
    if (firstThumbnail) {
        firstThumbnail.classList.add('active');
    }

    // Thumbnail click handlers
    document.querySelectorAll('.thumbnail-card').forEach(function(thumbnail) {
        thumbnail.addEventListener('click', function() {
            var slideIndex = parseInt(this.getAttribute('data-slide-index'));
            mainSwiper.slideTo(slideIndex);
            updateThumbnailActive(slideIndex);
        });
    });

    function updateThumbnailActive(index) {
        // Remove active class from all thumbnails
        document.querySelectorAll('.thumbnail-card').forEach(function(thumb) {
            thumb.classList.remove('active');
            thumb.querySelector('img').style.opacity = '0.6';
        });

        // Add active class to current thumbnail
        var activeThumb = document.querySelector('.thumbnail-card[data-slide-index="' + index + '"]');
        if (activeThumb) {
            activeThumb.classList.add('active');
            activeThumb.querySelector('img').style.opacity = '1';
        }
    }

    // Initialize Magnific Popup on all main images
    if (typeof initMagnificPopup === 'function') {
        initMagnificPopup('.main-image-popup');
    }
    @endif
});
</script>
@endpush
@endsection
