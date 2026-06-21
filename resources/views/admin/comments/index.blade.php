@extends('admin.layouts.app')

@section('content')

<div
    x-data="liveComments()"
    x-init="init()"
    class="max-w-7xl mx-auto relative"
>

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-white">
            Live Comments
        </h1>

        <span class="text-xs text-gray-500">
            Auto updating
        </span>
    </div>

    {{-- LOADING OVERLAY --}}
    <div
        x-show="loading"
        x-transition.opacity
        class="absolute inset-0 bg-black/40 flex items-center justify-center z-50 rounded-xl"
        x-cloak
    >
        <div class="bg-gray-900 border border-gray-700 px-4 py-2 rounded text-sm text-gray-300 shadow">
            Updating...
        </div>
    </div>

    {{-- CONTENT --}}
    <div
        id="comments-container"
        x-transition
    >
        @include('admin.comments._list')
    </div>

    {{-- STATUS --}}
    <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
        <span>Auto refreshing every 5 seconds...</span>

        {{-- LIVE INDICATOR --}}
        <span class="flex items-center gap-1 text-green-400">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            Live
        </span>
    </div>

</div>

@endsection