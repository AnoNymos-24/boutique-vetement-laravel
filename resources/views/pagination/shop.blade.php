@if ($paginator->hasPages())
    <div class="shop-flex shop-justify-between shop-items-center" style="margin-top: var(--shop-space-xl);">
        <span class="shop-text-muted" style="font-size:0.8125rem;">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </span>

        <div class="shop-flex shop-gap-sm">
            @if ($paginator->onFirstPage())
                <span class="shop-btn shop-btn--secondary shop-btn--sm" style="opacity:.4; cursor:not-allowed;"><x-icon name="arrow-left" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="shop-btn shop-btn--secondary shop-btn--sm"><x-icon name="arrow-left" /></a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="shop-btn shop-btn--secondary shop-btn--sm"><x-icon name="arrow-right" /></a>
            @else
                <span class="shop-btn shop-btn--secondary shop-btn--sm" style="opacity:.4; cursor:not-allowed;"><x-icon name="arrow-right" /></span>
            @endif
        </div>
    </div>
@endif
