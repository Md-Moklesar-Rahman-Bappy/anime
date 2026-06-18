@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">

    <h1 class="text-2xl font-semibold text-white mb-6">
        Settings
    </h1>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Site Info -->
        <div>
            <label class="text-gray-400 text-sm">Site Name</label>
            <input type="text" name="site_name"
                value="{{ $settings['site_name'] ?? config('app.name') }}"
                class="form-input">
        </div>

        <div>
            <label class="text-gray-400 text-sm">Site Description</label>
            <textarea name="site_description" rows="3" class="form-input">
{{ $settings['site_description'] ?? '' }}
            </textarea>
        </div>

        <div>
            <label class="text-gray-400 text-sm">Footer Text</label>
            <input type="text" name="footer_text"
                value="{{ $settings['footer_text'] ?? '' }}"
                class="form-input">
        </div>

        <hr class="border-gray-800">

        <!-- Logo -->
        <div>
            <label class="text-gray-400 text-sm mb-2 block">Logo</label>

            <input type="file" name="logo"
                class="file-input">

            @php
                $logoPreview = !empty($settings['logo'])
                    ? (\Illuminate\Support\Str::startsWith($settings['logo'], 'http')
                        ? $settings['logo']
                        : \Illuminate\Support\Facades\Storage::url($settings['logo']))
                    : null;
            @endphp

            @if($logoPreview)
                <div class="mt-3">
                    <p class="text-xs text-gray-500">Current:</p>
                    <img src="{{ $logoPreview }}" class="max-h-16 rounded border border-gray-700 mt-1">
                </div>
            @endif
        </div>

        <!-- Favicon -->
        <div>
            <label class="text-gray-400 text-sm mb-2 block">Favicon</label>

            <input type="file" name="favicon"
                class="file-input">

            @php
                $faviconPreview = !empty($settings['favicon'])
                    ? (\Illuminate\Support\Str::startsWith($settings['favicon'], 'http')
                        ? $settings['favicon']
                        : \Illuminate\Support\Facades\Storage::url($settings['favicon']))
                    : null;
            @endphp

            @if($faviconPreview)
                <div class="mt-3">
                    <p class="text-xs text-gray-500">Current:</p>
                    <img src="{{ $faviconPreview }}" class="max-h-10 rounded border border-gray-700 mt-1">
                </div>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg transition">
            Save Settings
        </button>

    </form>

    <!-- Sitemap -->
    <div class="mt-8 p-4 bg-[#111827] border border-gray-800 rounded-2xl">

        <p class="text-gray-300 text-sm mb-2">Sitemap</p>

        <p class="text-gray-500 text-xs mb-3">
            XML sitemap is automatically generated for all public pages.
        </p>

        <a href="{{ url('/sitemap.xml') }}" target="_blank"
           class="inline-block bg-[#1f2937] hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
            View Sitemap
        </a>

    </div>

</div>

<style>
.form-input {
    @apply w-full mt-1 px-3 py-2 bg-[#1f2937] border border-gray-700 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500;
}

.file-input {
    @apply w-full text-sm text-gray-400 file:mr-3 file:px-4 file:py-2 file:bg-indigo-600 file:text-white file:border-0 file:rounded-lg hover:file:bg-indigo-500;
}
</style>

@endsection