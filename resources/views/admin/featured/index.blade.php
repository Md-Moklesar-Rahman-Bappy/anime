@extends('admin.layouts.app')

@section('title', 'Featured Slider')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- HEADER --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        Featured Slider
    </h1>

    {{-- CARD --}}
    <div class="table-card p-6">

        {{-- TITLE --}}
        <h2 class="text-lg font-medium text-white mb-2">
            Auto Fill
        </h2>

        {{-- DESCRIPTION --}}
        <p class="text-sm text-gray-400 mb-6">
            Populate the homepage slider automatically from your anime list.
        </p>

        {{-- ACTIONS --}}
        <div class="flex flex-wrap gap-3">

            {{-- TOP RATED --}}
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="top_rated">
                <input type="hidden" name="count" value="5">

                <button type="submit"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-sm font-medium">
                    Top Rated
                </button>
            </form>

            {{-- MOST POPULAR --}}
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="most_viewed">
                <input type="hidden" name="count" value="5">

                <button type="submit"
                        class="btn-primary text-sm font-medium">
                    Most Popular
                </button>
            </form>

            {{-- RECENT --}}
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="recent">
                <input type="hidden" name="count" value="5">

                <button type="submit"
                        class="btn-success text-sm font-medium">
                    Recent Uploads
                </button>
            </form>

        </div>

    </div>

</div>

@endsection