@if(isset($block->data->slides) && is_array($block->data->slides) && count($block->data->slides) > 0)
<section class="">
    <div class="swiper swiper-{{ $block->id }}">
        <div class="swiper-wrapper">
            @foreach($block->data->slides as $slide)
            <div class="swiper-slide">
                @if(isset($slide->image))
                <!-- Desktop Image -->
                <img src="{{ asset($slide->image) }}"
                     class="d-block d-lg-none w-100"
                     alt="{{ $slide->title ?? 'Slide' }}"
                     style="max-height: 500px; object-fit: cover;">

                @if(isset($slide->image_mobile))
                <!-- Mobile Image -->
                <img src="{{ asset($slide->image_mobile) }}"
                     class="d-none d-lg-block w-100"
                     alt="{{ $slide->title ?? 'Slide' }}"
                     style="max-height: 500px; object-fit: cover;">
                @else
                <!-- Fallback: Use desktop image for both -->
                <img src="{{ asset($slide->image) }}"
                     class="d-none d-lg-block w-100"
                     alt="{{ $slide->title ?? 'Slide' }}"
                     style="max-height: 500px; object-fit: cover;">
                @endif
                @endif
                @if(isset($slide->title) || isset($slide->description))
                <div class="swiper-caption {{ $block->data->caption_position ?? '' }}">
                    <div class="{{ $block->data->caption_bg ?? 'bg-dark bg-opacity-50' }} p-4 rounded text-white">
                        @if(isset($slide->title))
                        <h2 class="display-6 fw-bold">{{ $slide->title }}</h2>
                        @endif
                        @if(isset($slide->description))
                        <p class="lead">{{ $slide->description }}</p>
                        @endif
                        @if(isset($slide->button_text) && isset($slide->button_url))
                        <a href="{{ $slide->button_url }}" class="btn btn-{{ $slide->button_color ?? 'primary' }} btn-lg mt-3">
                            {{ $slide->button_text }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @if(count($block->data->slides) > 1)
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        @endif
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.swiper-{{ $block->id }}', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-{{ $block->id }} .swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-{{ $block->id }} .swiper-button-next',
            prevEl: '.swiper-{{ $block->id }} .swiper-button-prev',
        },
    });
});
</script>
@endpush
@endif
