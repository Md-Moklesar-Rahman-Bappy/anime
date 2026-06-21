@extends('admin.layouts.app')

@section('content')

<div
    x-data="genresManager()"
    x-init="init()"
    class="max-w-6xl mx-auto relative"
>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">

        <h1 class="text-xl font-semibold text-white">
            Genres
        </h1>

        <button
            type="button"
            @click="importGenres()"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>

            Import from MAL
        </button>

    </div>

    {{-- SEARCH + CREATE --}}
    <div class="form-card mb-6 grid md:grid-cols-2 gap-4">

        {{-- SEARCH --}}
        <div>
            <label class="form-label">Search genres</label>
            <input
                type="text"
                x-model="search"
                @input.debounce.400ms="load()"
                placeholder="Search genre..."
                class="form-input"
            >
        </div>

        {{-- CREATE --}}
        <div>
            <label class="form-label">Add new genre</label>

            <div class="flex gap-3">
                <input
                    type="text"
                    x-model="name"
                    @keydown.enter.prevent="addGenre()"
                    placeholder="Genre name..."
                    class="form-input"
                >

                <button
                    type="button"
                    @click="addGenre()"
                    :disabled="saving"
                    class="btn-primary text-sm whitespace-nowrap disabled:opacity-60"
                >
                    <span x-show="!saving">Add</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

    </div>

    {{-- LOADING --}}
    <div
        x-show="loading"
        x-transition.opacity
        class="absolute inset-0 bg-black/40 flex items-center justify-center z-50 rounded-xl"
        x-cloak
    >
        <div class="bg-gray-900 border border-gray-700 px-4 py-2 rounded text-sm text-gray-300">
            Loading...
        </div>
    </div>

    {{-- LIST --}}
    <div id="genres-list" @click="handlePagination($event)">
        @include('admin.genres._list')
    </div>

</div>

@endsection