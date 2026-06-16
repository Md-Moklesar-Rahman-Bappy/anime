@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" 
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

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-white">Anime</h1>

        <a href="{{ route('admin.anime.create') }}"
           class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm transition">
            Add Anime
        </a>
    </div>

    <!-- Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl shadow">

        <!-- Search -->
        <div class="p-4 border-b border-gray-800">
            <div class="relative">

                <input
                    type="text"
                    x-model="search"
                    x-on:input.debounce.300ms="fetchResults(1)"
                    placeholder="Search anime..."
                    class="w-full bg-[#1f2937] border border-gray-700 rounded-lg pl-10 pr-10 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500"
                >

                <!-- Search icon -->
                <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <!-- Clear -->
                <button 
                    x-show="search.length"
                    x-on:click="search=''; fetchResults(1)"
                    class="absolute right-3 top-2.5 text-gray-500 hover:text-white">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                <tr class="bg-[#0f172a] text-gray-400 border-b border-gray-800">
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Episodes</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
                </thead>

                <tbody id="anime-table-body">
                    @include('admin.anime._table')
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div id="anime-pagination"
             class="p-4 border-t border-gray-800"
             x-on:click.prevent="handleClick($event)">
             
            @include('admin.anime._pagination')
        </div>

    </div>
</div>
@endsection
