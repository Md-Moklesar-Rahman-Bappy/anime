@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isset($anime) ? 'Edit' : 'Create' }} Anime</h1>
    <form action="{{ isset($anime) ? route('admin.anime.update', $anime) : route('admin.anime.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf @if(isset($anime)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Title</label><input type="text" name="title" value="{{ old('title', $anime->title ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" required></div>
            <div><label class="block text-sm text-gray-400 mb-1">Type</label><select name="type" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="">Select</option><option value="TV" @selected(old('type', $anime->type ?? '')=='TV')>TV</option><option value="Movie" @selected(old('type', $anime->type ?? '')=='Movie')>Movie</option><option value="OVA" @selected(old('type', $anime->type ?? '')=='OVA')>OVA</option><option value="ONA" @selected(old('type', $anime->type ?? '')=='ONA')>ONA</option><option value="Special" @selected(old('type', $anime->type ?? '')=='Special')>Special</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Status</label><select name="status" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="">Select</option><option value="Ongoing" @selected(old('status', $anime->status ?? '')=='Ongoing')>Ongoing</option><option value="Completed" @selected(old('status', $anime->status ?? '')=='Completed')>Completed</option><option value="Upcoming" @selected(old('status', $anime->status ?? '')=='Upcoming')>Upcoming</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Year</label><input type="number" name="year" value="{{ old('year', $anime->year ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Season</label><select name="season" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="">Select</option><option value="Winter" @selected(old('season', $anime->season ?? '')=='Winter')>Winter</option><option value="Spring" @selected(old('season', $anime->season ?? '')=='Spring')>Spring</option><option value="Summer" @selected(old('season', $anime->season ?? '')=='Summer')>Summer</option><option value="Fall" @selected(old('season', $anime->season ?? '')=='Fall')>Fall</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Rating (out of 10)</label><input type="number" step="0.1" name="rating" value="{{ old('rating', $anime->rating ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Duration (min)</label><input type="number" name="duration" value="{{ old('duration', $anime->duration ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Source</label><input type="text" name="source" value="{{ old('source', $anime->source ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Studio</label><input type="text" name="studio" value="{{ old('studio', $anime->studio ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Country</label><input type="text" name="country" value="{{ old('country', $anime->country ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Description</label><textarea name="description" rows="4" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ old('description', $anime->description ?? '') }}</textarea></div>
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Thumbnail</label><input type="file" name="thumbnail" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white hover:file:bg-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Banner</label><input type="file" name="banner" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white hover:file:bg-gray-700"></div>
            <div class="flex items-end"><label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="featured" value="1" @checked(old('featured', $anime->featured ?? false)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>Featured</span></label></div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Genres</label><div class="grid grid-cols-4 gap-2">@foreach($genres as $genre)<label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="genres[]" value="{{ $genre->id }}" @checked(isset($anime) && $anime->genres->contains($genre->id)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>{{ $genre->name }}</span></label>@endforeach</div></div>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">{{ isset($anime) ? 'Update' : 'Create' }}</button>
    </form>
</div>
@endsection
