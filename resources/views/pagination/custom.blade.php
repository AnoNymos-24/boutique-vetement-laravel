@if ($paginator->hasPages())
    <div class="pagination">
        <span>
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </span>

        <div class="flex gap-sm">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm" style="opacity:.5; cursor:not-allowed;"><x-icon name="chevron-left" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm"><x-icon name="chevron-left" /></a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm"><x-icon name="chevron-right" /></a>
            @else
                <span class="btn btn-secondary btn-sm" style="opacity:.5; cursor:not-allowed;"><x-icon name="chevron-right" /></span>
            @endif
        </div>
    </div>
@endif
