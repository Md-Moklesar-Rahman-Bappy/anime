@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isset($episode) ? 'Edit' : 'Create' }} Episode for {{ $anime->title }}</h1>
    <form action="{{ isset($episode) ? route('admin.anime.episodes.update', [$anime, $episode]) : route('admin.anime.episodes.store', $anime) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf @if(isset($episode)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Episode Number</label><input type="number" name="number" value="{{ old('number', $episode->number ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700" required></div>
            <div><label class="block text-sm text-gray-400 mb-1">Duration (seconds)</label><input type="number" name="duration" value="{{ old('duration', $episode->duration ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Title</label><input type="text" name="title" value="{{ old('title', $episode->title ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        <div><label class="block text-sm text-gray-400 mb-1">Description</label><textarea name="description" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ old('description', $episode->description ?? '') }}</textarea></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Video File (MP4)</label><input type="file" name="video" accept="video/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Or Video URL</label><input type="text" name="video_path" value="{{ old('video_path', $episode->video_path ?? '') }}" placeholder="https://..." class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Storage Disk</label><select name="storage_disk" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="local" @selected(old('storage_disk', $episode->storage_disk ?? '')=='local')>Local</option><option value="s3" @selected(old('storage_disk', $episode->storage_disk ?? '')=='s3')>S3</option><option value="streaming" @selected(old('storage_disk', $episode->storage_disk ?? '')=='streaming')>Streaming API</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Thumbnail</label><input type="file" name="thumbnail" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white"></div>
            <div class="flex items-end space-x-4">
                <label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="has_sub" value="1" @checked(old('has_sub', $episode->has_sub ?? true)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>Sub</span></label>
                <label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="has_dub" value="1" @checked(old('has_dub', $episode->has_dub ?? false)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>Dub</span></label>
            </div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Air Date</label><input type="date" name="air_date" value="{{ old('air_date', $episode->air_date ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>

        <div x-data="{ servers: {{ isset($episode) && $episode->servers ? $episode->servers->count() : 0 }}}" class="space-y-2">
            <div class="flex items-center justify-between"><label class="block text-sm text-gray-400">Video Servers</label><button type="button" @click="servers++" class="text-purple-500 text-sm">+ Add Server</button></div>
            <template x-for="(s, i) in servers" :key="i">
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" name="server_label[]" placeholder="Label (e.g. Server 1)" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                    <input type="url" name="server_url[]" placeholder="URL" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700">
                    <select name="server_type[]" class="bg-gray-800 text-white rounded-lg px-3 py-2 text-sm border border-gray-700"><option value="mp4">MP4</option><option value="m3u8">HLS</option><option value="embed">Embed</option></select>
                </div>
            </template>
        </div>

        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">{{ isset($episode) ? 'Update' : 'Create' }}</button>
    </form>
</div>
@endsection
