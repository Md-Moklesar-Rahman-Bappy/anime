@if ($paginator->hasPages())
<nav class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-4">

    <div class="small" style="color:#9ca3af">
        Showing
        <span style="color:#d1d5db;font-weight:500">{{ $paginator->firstItem() }}</span>
        to
        <span style="color:#d1d5db;font-weight:500">{{ $paginator->lastItem() }}</span>
        of
        <span style="color:#d1d5db;font-weight:500">{{ $paginator->total() }}</span>
    </div>

    <ul class="pagination pagination-sm mb-0 gap-1">
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link" style="background:#111827;border-color:#374151;color:#6b7280;border-radius:0.5rem;">‹</span>
            </li>
        @else
            <li class="page-item">
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link" style="background:#111827;border-color:#374151;color:#d1d5db;border-radius:0.5rem;">‹</a>
            </li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link" style="background:#111827;border-color:#374151;color:#6b7280;border-radius:0.5rem;">{{ $element }}</span>
                </li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link" style="background:#4f46e5;border-color:#6366f1;color:#fff;border-radius:0.5rem;">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a href="{{ $url }}" class="page-link" style="background:#111827;border-color:#374151;color:#d1d5db;border-radius:0.5rem;">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link" style="background:#111827;border-color:#374151;color:#d1d5db;border-radius:0.5rem;">›</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link" style="background:#111827;border-color:#374151;color:#6b7280;border-radius:0.5rem;">›</span>
            </li>
        @endif
    </ul>

</nav>
@endif
