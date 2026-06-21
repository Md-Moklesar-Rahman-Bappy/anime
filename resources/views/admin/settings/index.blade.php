@extends('admin.layouts.app')

@section('content')

@php
    $logoPreview = !empty($settings['logo'])
        ? (\Illuminate\Support\Str::startsWith($settings['logo'], 'http')
            ? $settings['logo']
            : \Illuminate\Support\Facades\Storage::url($settings['logo']))
        : null;

    $faviconPreview = !empty($settings['favicon'])
        ? (\Illuminate\Support\Str::startsWith($settings['favicon'], 'http')
            ? $settings['favicon']
            : \Illuminate\Support\Facades\Storage::url($settings['favicon']))
        : null;

    $siteName = $settings['site_name'] ?? config('app.name', 'AniKoto');
    $siteDescription = $settings['site_description'] ?? '';
    $footerText = $settings['footer_text'] ?? '';
@endphp

<div
    class="max-w-6xl mx-auto"
    x-data="settingsPreview({
        siteName: @js($siteName),
        siteDescription: @js($siteDescription),
        footerText: @js($footerText),
        logoPreview: @js($logoPreview),
        faviconPreview: @js($faviconPreview)
    })"
>

    {{-- TITLE --}}
    <h1 class="text-xl font-semibold text-white mb-6">
        Settings
    </h1>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- FORM --}}
        <div class="lg:col-span-2">

            <form
                action="{{ route('admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                x-data="adminForm({ key: 'settings_form' })"
                x-init="init()"
                @input.debounce.500ms="saveDraft()"
                @change.debounce.500ms="saveDraft()"
                @submit="submit($event)"
            >
                @csrf

                {{-- GENERAL SETTINGS --}}
                <div class="form-card space-y-4">

                    <h2 class="text-lg font-medium text-white">
                        General
                    </h2>

                    {{-- SITE NAME --}}
                    <div>
                        <label class="form-label">Site Name</label>
                        <input
                            type="text"
                            name="site_name"
                            x-model="$root.siteName"
                            class="form-input"
                        >
                    </div>

                    {{-- DESCRIPTION --}}
                    <div>
                        <label class="form-label">Site Description</label>
                        <textarea
                            name="site_description"
                            rows="3"
                            x-model="$root.siteDescription"
                            class="form-input"
                        ></textarea>
                    </div>

                    {{-- FOOTER --}}
                    <div>
                        <label class="form-label">Footer Text</label>
                        <input
                            type="text"
                            name="footer_text"
                            x-model="$root.footerText"
                            class="form-input"
                        >
                    </div>

                </div>

                {{-- MEDIA --}}
                <div class="form-card mt-6 space-y-6">

                    <h2 class="text-lg font-medium text-white">
                        Branding
                    </h2>

                    {{-- LOGO --}}
                    <div>
                        <label class="form-label mb-2">Logo</label>

                        <input
                            type="file"
                            name="logo"
                            accept="image/*"
                            class="form-input text-gray-400"
                            @change="$root.handleLogo($event)"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: transparent PNG or WebP.
                        </p>
                    </div>

                    {{-- FAVICON --}}
                    <div>
                        <label class="form-label mb-2">Favicon</label>

                        <input
                            type="file"
                            name="favicon"
                            accept="image/*"
                            class="form-input text-gray-400"
                            @change="$root.handleFavicon($event)"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: square image, 64×64 or higher.
                        </p>
                    </div>

                </div>

                {{-- ACTION --}}
                <div class="mt-6">
                    <button type="submit" class="btn-primary">
                        Save Settings
                    </button>
                </div>

            </form>

            {{-- SITEMAP --}}
            <div class="table-card mt-6 p-4">

                <p class="text-white mb-1">
                    Sitemap
                </p>

                <p class="text-sm text-gray-500 mb-3">
                    XML sitemap is automatically generated for all public pages.
                </p>

                <a
                    href="{{ url('/sitemap.xml') }}"
                    target="_blank"
                    class="btn-cancel text-sm inline-block"
                >
                    View Sitemap
                </a>

            </div>

        </div>

        {{-- LIVE PREVIEW --}}
        <div class="lg:col-span-1">

            <div class="sticky top-6 table-card p-5">

                <h2 class="text-lg font-medium text-white mb-4">
                    Live Preview
                </h2>

                {{-- LOGO PREVIEW --}}
                <div class="bg-[#0a0a0f] border border-gray-700 rounded-xl p-4 mb-4">

                    <div class="flex items-center gap-3">

                        <template x-if="logoPreview">
                            <img
                                :src="logoPreview"
                                class="h-12 max-w-[160px] object-contain rounded"
                                alt="Logo preview"
                            >
                        </template>

                        <template x-if="!logoPreview">
                            <div class="w-12 h-12 rounded bg-indigo-600 flex items-center justify-center text-white font-bold">
                                🎬
                            </div>
                        </template>

                        <div>
                            <p class="text-white font-semibold" x-text="siteName || 'AniKoto'"></p>
                            <p class="text-xs text-gray-500">
                                Header preview
                            </p>
                        </div>

                    </div>

                </div>

                {{-- FAVICON PREVIEW --}}
                <div class="bg-[#0a0a0f] border border-gray-700 rounded-xl p-4 mb-4">

                    <p class="text-xs text-gray-500 mb-2">
                        Favicon
                    </p>

                    <template x-if="faviconPreview">
                        <img
                            :src="faviconPreview"
                            class="w-10 h-10 rounded border border-gray-700 object-cover"
                            alt="Favicon preview"
                        >
                    </template>

                    <template x-if="!faviconPreview">
                        <div class="w-10 h-10 rounded border border-gray-700 bg-gray-800 flex items-center justify-center">
                            🎬
                        </div>
                    </template>

                </div>

                {{-- SITE TEXT PREVIEW --}}
                <div class="bg-[#0a0a0f] border border-gray-700 rounded-xl p-4">

                    <p class="text-white font-semibold mb-1" x-text="siteName || 'AniKoto'"></p>

                    <p class="text-sm text-gray-400 mb-3" x-text="siteDescription || 'Your site description will appear here.'"></p>

                    <div class="border-t border-gray-700 pt-3">
                        <p class="text-xs text-gray-500" x-text="footerText || 'Footer text preview.'"></p>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection