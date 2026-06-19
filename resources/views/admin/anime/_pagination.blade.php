@if ($animeList->hasPages())
<div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-3">

    <p class="small mb-0" style="color:#9ca3af">
        Showing {{ $animeList->firstItem() }}–{{ $animeList->lastItem() }} of {{ $animeList->total() }}
    </p>

    <div class="d-flex align-items-center gap-1">

        @if ($animeList->onFirstPage())
            <span class="btn btn-sm" style="color:#6b7280;background:#111827;border-radius:0.5rem;cursor:not-allowed">
                Prev
            </span>
        @else
            <button data-page="{{ $animeList->currentPage() - 1 }}"
                class="btn btn-sm paginate-link" style="color:#d1d5db;background:#1f2937;border-radius:0.5rem">
                Prev
            </button>
        @endif

        @php
            $current = $animeList->currentPage();
            $last = $animeList->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
        @endphp

        @if ($start > 1)
            <span class="px-2" style="color:#6b7280">...</span>
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="btn btn-sm" style="background:#4f46e5;color:#fff;border-radius:0.5rem;font-weight:600">
                    {{ $page }}
                </span>
            @else
                <button data-page="{{ $page }}"
                    class="btn btn-sm paginate-link" style="color:#d1d5db;background:#1f2937;border-radius:0.5rem">
                    {{ $page }}
                </button>
            @endif
        @endfor

        @if ($end < $last)
            <span class="px-2" style="color:#6b7280">...</span>
        @endif

        @if ($animeList->hasMorePages())
            <button data-page="{{ $animeList->currentPage() + 1 }}"
                class="btn btn-sm paginate-link" style="color:#d1d5db;background:#1f2937;border-radius:0.5rem">
                Next
            </button>
        @else
            <span class="btn btn-sm" style="color:#6b7280;background:#111827;border-radius:0.5rem;cursor:not-allowed">
                Next
            </span>
        @endif

    </div>
</div>
@endif