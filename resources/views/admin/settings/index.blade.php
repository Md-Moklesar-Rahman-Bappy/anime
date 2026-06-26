@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Settings</h1>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Site Name</label>
            <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Site Description</label>
            <textarea name="site_description" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ $settings['site_description'] ?? '' }}</textarea>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Footer Text</label>
            <input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">
        </div>

        <hr class="border-gray-700">

        <div>
            <label class="block text-sm text-gray-400 mb-2">Upload Logo</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="w-full text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
            @php $logoPreview = $settings['logo'] ?? null ? (\Illuminate\Support\Str::startsWith($settings['logo'], 'http') ? $settings['logo'] : \Illuminate\Support\Facades\Storage::url($settings['logo'])) : null; @endphp
            @if($logoPreview)
                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Current Logo:</p>
                    <img src="{{ $logoPreview }}" class="max-h-16 rounded border border-gray-700">
                </div>
            @endif
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-2">Upload Favicon</label>
            <input type="file" name="favicon" accept="image/png,image/x-icon,image/svg+xml,image/vnd.microsoft.icon" class="w-full text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
            @php $faviconPreview = $settings['favicon'] ?? null ? (\Illuminate\Support\Str::startsWith($settings['favicon'], 'http') ? $settings['favicon'] : \Illuminate\Support\Facades\Storage::url($settings['favicon'])) : null; @endphp
            @if($faviconPreview)
                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Current Favicon:</p>
                    <img src="{{ $faviconPreview }}" class="max-h-10 rounded border border-gray-700">
                </div>
            @endif
        </div>

        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Save Settings</button>
    </form>

    <div class="mt-8 p-4 bg-gray-800 rounded-lg border border-gray-700">
        <p class="text-gray-300 text-sm mb-2">Sitemap</p>
        <p class="text-gray-500 text-xs mb-3">Your XML sitemap is automatically generated and includes all public pages, anime, manga, genres, and listing pages.</p>
        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
            View Sitemap
        </a>
    </div>
</div>
@endsection
