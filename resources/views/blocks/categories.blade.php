@if(isset($categories) && $categories->count() > 0)
<section class="py-5">
    <div class="container">
        @if(isset($block->data->title))
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">{{ $block->data->title }}</h2>
            @if(isset($block->data->subtitle))
            <p class="lead text-muted">{{ $block->data->subtitle }}</p>
            @endif
        </div>
        @endif

        <div class="row g-4">
            @foreach($categories->take(4) as $category)
            <div class="col-sm">
                <a href="{{ url('/productos?categories[]=' . $category->slug) }}" class="text-decoration-none">
                    <div class="ratio ratio-4x3 rounded shadow-sm overflow-hidden position-relative"
                         @if($category->mainImage)
                         style="background-image: url('{{ $category->mainImage->file_url }}'); background-size: cover; background-position: center;"
                         @endif>
                        <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex flex-column justify-content-end p-3 align-items-center"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                            <h5 class="text-white mb-3 fw-bold">{{ $category->name }}</h5>
                            <span class="btn btn-outline-light btn-sm">Ver más</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
