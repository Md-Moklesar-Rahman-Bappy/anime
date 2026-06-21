@extends('admin.layouts.app')

@section('content')

<div
    x-data="ajaxPagination({ target: 'manga-list' })"
    x-init="init()"
    class="max-w-7xl mx-auto relative"
>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">

        <h1 class="text-xl font-semibold text-white">
            Manga
        </h1>

        <a href="{{ route('admin.manga.create') }}"
           class="btn-success">
            Add Manga
        </a>

    </div>

    {{-- SEARCH --}}
    <form
        action="{{ route('admin.manga.index') }}"
        method="GET"
        @submit.prevent="filter($event)"
        class="form-card mb-6"
    >
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search manga..."
               class="form-input">
    </form>

    {{-- LOADING --}}
    <div x-show="loading"
         class="absolute inset-0 bg-black/40 flex items-center justify-center z-50"
         x-cloak>
        <div class="bg-gray-900 px-4 py-2 rounded">
            Loading...
        </div>
    </div>

    {{-- CONTENT --}}
    <div id="manga-list">
        @include('admin.manga._list')
    </div>

</div>

@endsection