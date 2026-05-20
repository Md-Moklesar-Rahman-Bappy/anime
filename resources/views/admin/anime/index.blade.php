@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="animeSearch()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Anime</h1>
        <a href="{{ route('admin.anime.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">Add New</a>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800">
        <div class="p-4 border-b border-gray-800">
            <div class="relative">
                <input type="text"
                    x-model="search"
                    x-on:input.debounce.300ms="fetchResults(1)"
                    placeholder="Search anime by title, type, status, studio..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-10 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-purple-500">
                <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button x-show="search.length > 0" x-on:click="search = ''; fetchResults(1)" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-800 bg-gray-900/50">
                        <th class="text-left p-3 font-medium">Title</th>
                        <th class="text-left p-3 font-medium">Type</th>
                        <th class="text-left p-3 font-medium">Status</th>
                        <th class="text-left p-3 font-medium">Episodes</th>
                        <th class="text-left p-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="anime-table-body">
                    @include('admin.anime._table')
                </tbody>
            </table>
        </div>

        <div id="anime-pagination" class="p-4 border-t border-gray-800" x-on:click.prevent="handleClick($event)">
            @include('admin.anime._pagination')
        </div>
    </div>

    <script>
        function animeSearch() {
            return {
                search: '{{ request('search') }}',
                fetchResults(page) {
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
                    if (link) {
                        this.fetchResults(link.dataset.page);
                    }
                }
            };
        }
    </script>
</div>
@endsection
