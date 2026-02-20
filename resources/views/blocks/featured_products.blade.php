@if($featuredProducts->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        @if(isset($block->data->title))
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">{{ $block->data->title }}</h2>
            @if(isset($block->data->subtitle))
            <p class="lead text-muted">{{ $block->data->subtitle }}</p>
            @endif
        </div>
        @else
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Productos Destacados</h2>
        </div>
        @endif

        <div class="row g-4">
            @foreach($featuredProducts as $product)
            <div class="col-lg-3 col-md-6">
                <x-product-card :product="$product" />
            </div>
            @endforeach
        </div>

        @if(isset($block->data->show_all_button) && $block->data->show_all_button)
        <div class="text-center mt-5">
            <a href="{{ url('/productos') }}" class="btn btn-primary">
                Ver Todos los Productos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif
