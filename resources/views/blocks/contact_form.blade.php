@if(isset($block->data->title))
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                {{-- Leaflet CSS --}}
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

                <div class="card shadow-sm">
                    <div class="card-body p-5">

                        <div class="row g-4 align-items-stretch">

                            {{-- FORM 50% --}}
                            <div class="col-md-6">

                                <div class="text-center mb-4">
                                    <h2 class="display-6 fw-bold">{{ $block->data->title }}</h2>

                                    @if(isset($block->data->description))
                                        <p class="lead text-muted">{{ $block->data->description }}</p>
                                    @else
                                        <p class="text-muted">
                                            Completá el formulario y un asesor te contactará a la brevedad.
                                        </p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('form.submit') }}">
                                    @csrf

                                    @if(isset($block->data->producto))
                                        <input name="producto" type="hidden" value="{{ $block->data->producto }}">
                                    @endif

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nombre y Apellido *</label>
                                            <input name="name" type="text" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email *</label>
                                            <input name="email" type="email" class="form-control" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Teléfono *</label>
                                            <input name="phone" type="text" class="form-control" required>
                                        </div>

                                        @if(isset($block->data->subject))
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Asunto</label>
                                            <input name="subject" type="text" class="form-control" value="{{ $block->data->subject }}">
                                        </div>
                                        @endif

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Mensaje *</label>
                                            <textarea name="message" class="form-control" rows="5" required></textarea>
                                        </div>

                                        @if(isset($block->data->email_recipient))
                                            <input type="hidden" name="recipient" value="{{ $block->data->email_recipient }}">
                                        @endif

                                        @if(isset($block->data->email_subject))
                                            <input type="hidden" name="email_subject" value="{{ $block->data->email_subject }}">
                                        @endif

                                        <div class="col-12">
                                            @captcha
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                {{ $block->data->button_text ?? 'Enviar Mensaje' }}
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>

                            {{-- MAPA LEAFLET 50% --}}
                            <div class="col-md-6">
                                <div class="position-relative rounded overflow-hidden" style="min-height:420px;">

                                    <div id="leafletMap" class="w-100 h-100" style="min-height:420px;"></div>

                                    <a
                                        href="https://www.google.com/maps/dir/?api=1&destination={{ $block->data->lat ?? -25.285447 }},{{ $block->data->lng ?? -57.635912 }}"
                                        target="_blank"
                                        class="btn btn-light shadow-lg rounded-pill px-4 py-2 fw-semibold position-absolute"
                                        style="bottom:20px; right:20px; z-index:1000;">
                                        <i class="fas fa-location-arrow me-2"></i>
                                        Ver cómo llegar
                                    </a>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- Leaflet JS --}}
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                <script>
                document.addEventListener("DOMContentLoaded", function () {

                    const lat = {{ $block->data->lat ?? -25.301015 }};
                    const lng = {{ $block->data->lng ?? -57.631030 }};

                    const map = L.map('leafletMap').setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    L.marker([lat, lng])
                        .addTo(map)
                        .bindPopup('Target Eyewear')
                        .openPopup();

                });
                </script>

            </div>
        </div>
    </div>
</section>
@endif