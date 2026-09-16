@if ($paginator->hasPages())
    <div class="shop-flex shop-justify-between shop-items-center" style="margin-top: var(--shop-space-xl);">
        <span class="shop-text-muted" style="font-size:0.8125rem;">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </span>

        <div class="shop-flex shop-gap-sm">
            @if ($paginator->onFirstPage())
                <span class="shop-btn shop-btn--secondary shop-btn--sm" style="opacity:.4; cursor:not-allowed;">Précédent</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="shop-btn shop-btn--secondary shop-btn--sm">Précédent</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="shop-btn shop-btn--secondary shop-btn--sm">Suivant</a>
            @else
                <span class="shop-btn shop-btn--secondary shop-btn--sm" style="opacity:.4; cursor:not-allowed;">Suivant</span>
            @endif
        </div>
    </div>
@endif
