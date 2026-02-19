<div class="pagination-grid">
    <div class="pagination-count">{{ $products->firstItem() }} - {{ $products->lastItem() }} de {{ $products->total() }} productos</div>
    @if(isset($products) && $products->hasPages())
        {{ $products->appends([
            'q' => $search ?? '',
            'categories' => $categories ?? [],
            'brands' => $brands ?? []
        ])->links('pagination::bootstrap-5') }}
    @endif
</div>
