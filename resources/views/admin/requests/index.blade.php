@extends('admin.layouts.app')

@section('content')

<div
    x-data="liveRequests()"
    x-init="init()"
    class="max-w-7xl mx-auto relative"
>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">

        <div>
            <h1 class="text-xl font-semibold text-white">
                Requests
            </h1>

            <p class="text-xs text-gray-500 mt-1">
                Auto refreshing every 7 seconds
            </p>
        </div>

        <button
            type="button"
            @click="bulkFulfill()"
            class="btn-success text-sm"
        >
            Mark Visible Pending as Fulfilled
        </button>

    </div>

    {{-- FILTERS --}}
    <div class="form-card mb-6 grid md:grid-cols-3 gap-4">

        <div>
            <label class="form-label">Search</label>
            <input
                type="text"
                x-model="search"
                @input.debounce.500ms="fetch()"
                placeholder="Anime title, user, description..."
                class="form-input"
            >
        </div>

        <div>
            <label class="form-label">Status</label>
            <select
                x-model="status"
                @change="fetch()"
                class="form-input"
            >
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="fulfilled">Fulfilled</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="flex items-end">
            <button
                type="button"
                @click="search=''; status=''; fetch()"
                class="btn-cancel w-full"
            >
                Reset
            </button>
        </div>

    </div>

    {{-- LOADING --}}
    <div
        x-show="loading"
        x-transition.opacity
        class="absolute inset-0 bg-black/40 flex items-center justify-center z-50 rounded-xl"
        x-cloak
    >
        <div class="bg-gray-900 border border-gray-700 px-4 py-2 rounded text-sm text-gray-300 shadow">
            Updating requests...
        </div>
    </div>

    {{-- LIST --}}
    <div id="requests-container" @click="handlePagination($event)">
        @include('admin.requests._list')
    </div>

</div>

@endsection