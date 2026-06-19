@extends('admin.layouts.app')

@section('content')
<div class="container" style="max-width:800px">

    <h1 class="h4 fw-semibold text-white mb-3">Settings</h1>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="small" style="color:#9ca3af">Site Name</label>
            <input type="text" name="site_name"
                value="{{ $settings['site_name'] ?? config('app.name') }}"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
        </div>

        <div class="mb-3">
            <label class="small" style="color:#9ca3af">Site Description</label>
            <textarea name="site_description" rows="3" class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">{{ $settings['site_description'] ?? '' }}</textarea>
        </div>

        <div class="mb-3">
            <label class="small" style="color:#9ca3af">Footer Text</label>
            <input type="text" name="footer_text"
                value="{{ $settings['footer_text'] ?? '' }}"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
        </div>

        <hr style="border-color:#374151">

        <div class="mb-3">
            <label class="small mb-2 d-block" style="color:#9ca3af">Logo</label>
            <input type="file" name="logo"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            @php
                $logoPreview = !empty($settings['logo'])
                    ? (\Illuminate\Support\Str::startsWith($settings['logo'], 'http')
                        ? $settings['logo']
                        : \Illuminate\Support\Facades\Storage::url($settings['logo']))
                    : null;
            @endphp
            @if($logoPreview)
                <div class="mt-2">
                    <p class="small" style="color:#6b7280">Current:</p>
                    <img src="{{ $logoPreview }}" style="max-height:4rem;border-radius:0.25rem;border:1px solid #4b5563;margin-top:0.25rem">
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label class="small mb-2 d-block" style="color:#9ca3af">Favicon</label>
            <input type="file" name="favicon"
                class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#9ca3af">
            @php
                $faviconPreview = !empty($settings['favicon'])
                    ? (\Illuminate\Support\Str::startsWith($settings['favicon'], 'http')
                        ? $settings['favicon']
                        : \Illuminate\Support\Facades\Storage::url($settings['favicon']))
                    : null;
            @endphp
            @if($faviconPreview)
                <div class="mt-2">
                    <p class="small" style="color:#6b7280">Current:</p>
                    <img src="{{ $faviconPreview }}" style="max-height:2.5rem;border-radius:0.25rem;border:1px solid #4b5563;margin-top:0.25rem">
                </div>
            @endif
        </div>

        <button type="submit" class="btn" style="background:#4f46e5;color:#fff">
            Save Settings
        </button>

    </form>

    <div class="mt-4 p-3" style="background:#111827;border:1px solid #374151;border-radius:1rem">
        <p class="mb-1" style="color:#d1d5db">Sitemap</p>
        <p class="small mb-2" style="color:#6b7280">
            XML sitemap is automatically generated for all public pages.
        </p>
        <a href="{{ url('/sitemap.xml') }}" target="_blank"
           class="btn btn-sm" style="background:#1f2937;color:#fff">
            View Sitemap
        </a>
    </div>

</div>

@endsection