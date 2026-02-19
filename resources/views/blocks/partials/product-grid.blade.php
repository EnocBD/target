@if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
            <div class="col-lg-4 col-md-6">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info text-center">
        <i class="fas fa-magnifying-glass display-4 text-muted mb-3"></i>
        <h5>No se encontraron productos</h5>
        <p>Intenta con otros filtros o términos de búsqueda.</p>
    </div>
@endif
