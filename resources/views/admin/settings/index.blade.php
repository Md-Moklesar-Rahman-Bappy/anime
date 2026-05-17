@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Settings</h1>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
        @csrf
        <div><label class="block text-sm text-gray-400 mb-1">Site Name</label><input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        <div><label class="block text-sm text-gray-400 mb-1">Site Description</label><textarea name="site_description" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ $settings['site_description'] ?? '' }}</textarea></div>
        <div><label class="block text-sm text-gray-400 mb-1">Footer Text</label><input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        <div><label class="block text-sm text-gray-400 mb-1">Logo URL</label><input type="url" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Save Settings</button>
    </form>
</div>
@endsection
