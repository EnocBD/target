@if(isset($block->data->content))
<section class="py-5 {{ $block->data->background_class ?? '' }}">
    <div class="container">
        @if(isset($block->data->title))
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">{{ $block->data->title }}</h2>
            @if(isset($block->data->subtitle))
            <p class="lead text-muted">{{ $block->data->subtitle }}</p>
            @endif
        </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8 {{ $block->data->text_align ?? 'text-center' }}">
                <div class="{{ $block->data->content_class ?? '' }}">
                    {!! $block->data->content !!}
                </div>

                @if(isset($block->data->button_text) && isset($block->data->button_url))
                <div class="mt-4">
                    <a href="{{ $block->data->button_url }}" class="btn btn-{{ $block->data->button_color ?? 'primary' }}">
                        {{ $block->data->button_text }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
