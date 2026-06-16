@extends('admin.layouts.app')

@section('title', 'Featured Slider')

@section('content')
<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        Featured Slider
    </h1>

    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 shadow">

        <h2 class="text-lg font-medium text-white mb-2">
            Auto Fill
        </h2>

        <p class="text-sm text-gray-400 mb-5">
            Populate the homepage slider automatically from your anime list.
        </p>

        <div class="flex flex-wrap gap-3">

            <!-- Top Rated -->
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="top_rated">
                <input type="hidden" name="count" value="5">

                <button class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm rounded-lg transition font-medium">
                    Top Rated
                </button>
            </form>

            <!-- Most Popular -->
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="most_viewed">
                <input type="hidden" name="count" value="5">

                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition font-medium">
                    Most Popular
                </button>
            </form>

            <!-- Recent -->
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                @csrf
                <input type="hidden" name="mode" value="recent">
                <input type="hidden" name="count" value="5">

                <button class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm rounded-lg transition font-medium">
                    Recent Uploads
                </button>
            </form>

        </div>

    </div>

</div>
@endsection
