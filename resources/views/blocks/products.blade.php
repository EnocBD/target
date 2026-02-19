@section('title', isset($block->data->title) ? $block->data->title : 'Productos - Target Eyewear')

<section class="py-5" id="products-block">
    <div class="container">
        @if(isset($block->data->title))
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">{{ $block->data->title }}</h1>
            @if(isset($block->data->subtitle))
            <p class="lead text-muted">{{ $block->data->subtitle }}</p>
            @endif
        </div>
        @endif

        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Filtros</h6>
                    </div>
                    <div class="card-body">
                        <form id="filters-form">
                            <!-- Search -->
                            <div class="mb-3">
                                <label for="q" class="form-label fw-bold">Buscar</label>
                                <input type="text" class="form-control" name="q" id="q" placeholder="Nombre, categoría o marca..." value="{{ request('q') }}">
                            </div>

                            <!-- Categories -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Categorías</label>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    @foreach($categories as $cat)
                                    <div class="form-check">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="categories[]" value="{{ $cat->slug }}" id="cat-{{ $cat->slug }}">
                                        <label class="form-check-label" for="cat-{{ $cat->slug }}">
                                            {{ $cat->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Brands -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Marcas</label>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    @foreach($brands as $br)
                                    <div class="form-check">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="brands[]" value="{{ $br->slug }}" id="brand-{{ $br->slug }}">
                                        <label class="form-check-label" for="brand-{{ $br->slug }}">
                                            {{ $br->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Active Filters -->
                            <div id="active-filters" class="mb-3" style="display: none;">
                                <label class="form-label fw-bold">Filtros Activos:</label>
                                <div id="active-filters-container" class="d-flex flex-wrap gap-1"></div>
                            </div>

                            <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-xmark-circle"></i> Limpiar Filtros
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div id="products-container">
                    <!-- Loading state -->
                    <div id="products-loading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <!-- Products content will be loaded here -->
                    <div id="products-content"></div>

                    <!-- Pagination -->
                    <div id="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filters-form');
    const searchInput = document.getElementById('q');
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const productsContent = document.getElementById('products-content');
    const productsLoading = document.getElementById('products-loading');
    const paginationContainer = document.getElementById('pagination-container');
    const activeFiltersContainer = document.getElementById('active-filters-container');
    const activeFiltersDiv = document.getElementById('active-filters');

    let searchTimeout;

    // Function to get all filter values
    function getFilters() {
        const formData = new FormData(filtersForm);
        const filters = {};

        // Get search query
        if (formData.get('q')) {
            filters.q = formData.get('q');
        }

        // Get selected categories
        const categories = formData.getAll('categories[]');
        if (categories.length > 0) {
            filters.categories = categories;
        }

        // Get selected brands
        const brands = formData.getAll('brands[]');
        if (brands.length > 0) {
            filters.brands = brands;
        }

        return filters;
    }

    // Function to load products via AJAX
    function loadProducts(page = 1) {
        const filters = getFilters();

        // Show loading
        productsLoading.style.display = 'block';
        productsContent.style.opacity = '0.5';

        // Build query parameters
        const params = new URLSearchParams();
        if (filters.q) params.append('q', filters.q);
        if (filters.categories) filters.categories.forEach(c => params.append('categories[]', c));
        if (filters.brands) filters.brands.forEach(b => params.append('brands[]', b));
        params.append('page', page);
        params.append('items_per_page', 12);

        fetch(`{{ route('products.filter') }}?${params.toString()}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                q: filters.q,
                categories: filters.categories || [],
                brands: filters.brands || [],
                page: page,
                items_per_page: 12
            })
        })
        .then(response => response.json())
        .then(data => {
            // Update products content
            productsContent.innerHTML = data.products;
            paginationContainer.innerHTML = data.pagination || '';

            // Update active filters display
            updateActiveFilters(filters);

            // Hide loading
            productsLoading.style.display = 'none';
            productsContent.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error loading products:', error);
            productsLoading.style.display = 'none';
            productsContent.style.opacity = '1';
        });
    }

    // Function to update active filters display
    function updateActiveFilters(filters) {
        activeFiltersContainer.innerHTML = '';
        let hasFilters = false;

        if (filters.q) {
            hasFilters = true;
            const badge = document.createElement('span');
            badge.className = 'badge bg-light text-dark';
            badge.innerHTML = `"${filters.q}" <button class="btn-close btn-close-sm ms-1" data-filter="q"></button>`;
            activeFiltersContainer.appendChild(badge);
        }

        if (filters.categories) {
            hasFilters = true;
            filters.categories.forEach(catSlug => {
                const checkbox = document.querySelector(`input[value="${catSlug}"]`);
                if (checkbox) {
                    const label = checkbox.nextElementSibling.textContent;
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-light text-dark';
                    badge.innerHTML = `${label} <button class="btn-close btn-close-sm ms-1" data-filter="categories" data-value="${catSlug}"></button>`;
                    activeFiltersContainer.appendChild(badge);
                }
            });
        }

        if (filters.brands) {
            hasFilters = true;
            filters.brands.forEach(brandSlug => {
                const checkbox = document.querySelector(`input[value="${brandSlug}"]`);
                if (checkbox) {
                    const label = checkbox.nextElementSibling.textContent;
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-light text-dark';
                    badge.innerHTML = `${label} <button class="btn-close btn-close-sm ms-1" data-filter="brands" data-value="${brandSlug}"></button>`;
                    activeFiltersContainer.appendChild(badge);
                }
            });
        }

        activeFiltersDiv.style.display = hasFilters ? 'block' : 'none';
    }

    // Debounced search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadProducts();
        }, 500);
    });

    // Checkbox change events
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            loadProducts();
        });
    });

    // Clear all filters
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        checkboxes.forEach(cb => cb.checked = false);
        loadProducts();
    });

    // Remove individual active filter
    activeFiltersContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            const filterType = e.target.dataset.filter;
            const filterValue = e.target.dataset.value;

            if (filterType === 'q') {
                searchInput.value = '';
            } else if (filterType === 'categories' || filterType === 'brands') {
                const checkbox = document.querySelector(`input[value="${filterValue}"]`);
                if (checkbox) checkbox.checked = false;
            }

            loadProducts();
        }
    });

    // Handle pagination clicks
    paginationContainer.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.getAttribute('href')) {
            e.preventDefault();
            const url = new URL(e.target.href, window.location.origin);
            const page = url.searchParams.get('page');
            if (page) {
                loadProducts(parseInt(page));
            }
        }
    });

    // Initialize filters from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('q');
    if (searchParam) {
        searchInput.value = searchParam;
    }

    // Initialize categories from URL parameters
    const categoryParams = urlParams.getAll('categories[]');
    categoryParams.forEach(catSlug => {
        const checkbox = document.querySelector(`input[name="categories[]"][value="${catSlug}"]`);
        if (checkbox) checkbox.checked = true;
    });

    // Initialize brands from URL parameters
    const brandParams = urlParams.getAll('brands[]');
    brandParams.forEach(brandSlug => {
        const checkbox = document.querySelector(`input[name="brands[]"][value="${brandSlug}"]`);
        if (checkbox) checkbox.checked = true;
    });

    // Initial load
    loadProducts();
});
</script>
@endpush
