@if(isset($block->data->steps) && is_array($block->data->steps) && count($block->data->steps) > 0)
<section class="py-5">
    <div class="container">

        @if(isset($block->data->title) || isset($block->data->description))
        <div class="mb-4">
            @if(isset($block->data->description))
            <small class="fw-semibold text-secondary">
                {{ $block->data->description }}
            </small>
            @endif

            @if(isset($block->data->title))
            <h2 class="fw-bold text-primary">
                {{ $block->data->title }}
            </h2>
            @endif
        </div>
        @endif

        <div class="row g-4">
            @foreach($block->data->steps as $step)
            <div class="col-lg-{{ 12 / min(4, count($block->data->steps)) }}">
                @if(isset($step->title) || isset($step->text))
                <div class="target-card" id="target-card-{{ $loop->iteration }}">
                    <div class="row g-3">
                        <div class="col-auto">
                            <span>{{ $loop->iteration }}</span>
                        </div>
                        <div class="col">
                            @if(isset($step->title))
                                <h6 class="mb-2">{{ $step->title }}</h6>
                            @endif

                            @if(isset($step->text))
                                <p class="mb-0 small">{{ $step->text }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
