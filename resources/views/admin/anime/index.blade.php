@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4"
    x-data="{
        search: '{{ request('search') }}',

        fetchResults(page = 1) {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            params.set('page', page);

            fetch('{{ route('admin.anime.search') }}?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('anime-table-body').innerHTML = data.html;
                document.getElementById('anime-pagination').innerHTML = data.pagination;
            });
        },

        handleClick(e) {
            const link = e.target.closest('.paginate-link');
            if (link) this.fetchResults(link.dataset.page);
        }
    }">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-semibold text-white">Anime</h1>

        <a href="{{ route('admin.anime.create') }}"
           class="btn btn-sm" style="background:#4f46e5;border-color:#4f46e5;color:#fff">
            Add Anime
        </a>
    </div>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem">

        <div class="p-3" style="border-bottom:1px solid #374151">
            <div class="position-relative">
                <input
                    type="text"
                    x-model="search"
                    x-on:input.debounce.300ms="fetchResults(1)"
                    placeholder="Search anime..."
                    class="form-control"
                    style="background:#1f2937;border:1px solid #4b5563;color:#fff;padding:0.625rem 2.5rem 0.625rem 2.5rem"
                >

                <svg class="position-absolute" style="left:0.75rem;top:0.75rem;width:1rem;height:1rem;color:#6b7280"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <button
                    x-show="search.length"
                    x-on:click="search=''; fetchResults(1)"
                    class="position-absolute btn btn-sm border-0"
                    style="right:0.5rem;top:0.5rem;color:#6b7280">

                    <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                    <th class="p-3 text-start">Title</th>
                    <th class="p-3 text-start">Type</th>
                    <th class="p-3 text-start">Status</th>
                    <th class="p-3 text-start">Episodes</th>
                    <th class="p-3 text-start">Actions</th>
                </tr>
                </thead>
                <tbody id="anime-table-body">
                    @include('admin.anime._table')
                </tbody>
            </table>
        </div>

        <div id="anime-pagination"
             class="p-3"
             style="border-top:1px solid #374151"
             x-on:click.prevent="handleClick($event)">
            @include('admin.anime._pagination')
        </div>

    </div>
</div>
@endsection
