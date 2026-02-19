@if(isset($block->data->phone) || isset($block->data->address) || isset($block->data->facebook) || isset($block->data->instagram))
<section class="py-5 bg-light">
    <div class="container">
        @if(isset($block->data->title))
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">{{ $block->data->title }}</h2>
        </div>
        @endif

        <div class="row g-4">
            @if(isset($block->data->phone))
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center"">
                        <div class="mb-3">
                            <i class="fas fa-phone text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">Tel&eacute;fono</h5>
                        <p class="card-text">
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $block->data->phone) }}" class="text-decoration-none">
                                {{ $block->data->phone }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($block->data->address))
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center"">
                        <div class="mb-3">
                            <i class="fas fa-location-dot text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">Direcci&oacute;n</h5>
                        <p class="card-text">
                            @if(isset($block->data->maps))
                            <a href="{{ $block->data->maps }}" target="_blank" class="text-decoration-none">
                                {{ $block->data->address }}
                            </a>
                            @else
                            {{ $block->data->address }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($block->data->facebook) || isset($block->data->instagram))
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center"">
                        <div class="mb-3">
                            <i class="fas fa-share-nodes text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">Redes Sociales</h5>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            @if(isset($block->data->facebook))
                            <a href="{{ $block->data->facebook }}" class="btn btn-outline-primary btn-lg" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif
                            @if(isset($block->data->instagram))
                            <a href="{{ $block->data->instagram }}" class="btn btn-outline-danger btn-lg" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif
                            @if(isset($block->data->youtube))
                            <a href="{{ $block->data->youtube }}" class="btn btn-outline-danger btn-lg" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endif
