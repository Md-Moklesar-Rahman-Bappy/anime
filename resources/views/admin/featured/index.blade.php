@extends('admin.layouts.app')

@section('title', 'Featured Slider')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold mb-6">Featured Slider</h1>

    <div class="bg-gray-900 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Auto-Fill</h2>
        <p class="text-sm text-gray-400 mb-4">Automatically populate the slider from existing anime.</p>
        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                @csrf
                <input type="hidden" name="mode" value="top_rated">
                <input type="hidden" name="count" value="5">
                <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm rounded-lg transition font-medium">Top Rated</button>
            </form>
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                @csrf
                <input type="hidden" name="mode" value="most_viewed">
                <input type="hidden" name="count" value="5">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition font-medium">Most Popular</button>
            </form>
            <form method="POST" action="{{ route('admin.featured.auto-fill') }}" class="inline">
                @csrf
                <input type="hidden" name="mode" value="recent">
                <input type="hidden" name="count" value="5">
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm rounded-lg transition font-medium">Recent Uploads</button>
            </form>
        </div>
    </div>
</div>
@endsection