@extends('admin.layouts.app')

@section('content')

<div
    x-data="ajaxPagination({
        target: 'anime-list',
        url: '{{ route('admin.anime.index') }}'
    })"
    x-init="init()"
    class="max-w-7xl mx-auto relative"
>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">

        <h1 class="text-xl font-semibold text-white">
            Anime
        </h1>

        <a href="{{ route('admin.anime.create') }}"
           class="btn-primary">
            Add Anime
        </a>

    </div>

    {{-- SEARCH --}}
    <form
        action="{{ route('admin.anime.index') }}"
        method="GET"
        @submit.prevent="filter($event)"
        class="form-card mb-6"
    >

        <div class="relative">

            {{-- INPUT --}}
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search anime..."
                class="form-input pl-10 pr-10"
            >

            {{-- SEARCH ICON --}}
            <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500 pointer-events-none"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            {{-- CLEAR BUTTON --}}
            <button
                type="button"
                x-show="$el.querySelector('input').value"
                @click="$el.querySelector('input').value = ''; filter($event)"
                class="absolute right-3 top-2 text-gray-500 hover:text-white text-sm"
            >
                ✕
            </button>

        </div>

    </form>

    {{-- LOADING OVERLAY --}}
    <div
        x-show="loading"
        x-transition
        class="absolute inset-0 bg-black/40 flex items-center justify-center z-50 rounded-xl"
        x-cloak
    >
        <div class="bg-gray-900 border border-gray-700 px-4 py-2 rounded text-sm text-gray-300">
            Loading...
        </div>
    </div>

    {{-- AJAX CONTENT --}}
    <div id="anime-list">
        @include('admin.anime._list')
    </div>

</div>

@endsection