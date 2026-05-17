@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isset($manga) ? 'Edit' : 'Create' }} Manga</h1>
    <form action="{{ isset($manga) ? route('admin.manga.update', $manga) : route('admin.manga.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf @if(isset($manga)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Title</label><input type="text" name="title" value="{{ old('title', $manga->title ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" required></div>
            <div><label class="block text-sm text-gray-400 mb-1">Type</label><select name="type" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="">Select</option><option value="Manga" @selected(old('type', $manga->type ?? '')=='Manga')>Manga</option><option value="Manhwa" @selected(old('type', $manga->type ?? '')=='Manhwa')>Manhwa</option><option value="Manhua" @selected(old('type', $manga->type ?? '')=='Manhua')>Manhua</option><option value="One-shot" @selected(old('type', $manga->type ?? '')=='One-shot')>One-shot</option><option value="Doujinshi" @selected(old('type', $manga->type ?? '')=='Doujinshi')>Doujinshi</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Status</label><select name="status" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"><option value="">Select</option><option value="Ongoing" @selected(old('status', $manga->status ?? '')=='Ongoing')>Ongoing</option><option value="Completed" @selected(old('status', $manga->status ?? '')=='Completed')>Completed</option><option value="Hiatus" @selected(old('status', $manga->status ?? '')=='Hiatus')>Hiatus</option><option value="Cancelled" @selected(old('status', $manga->status ?? '')=='Cancelled')>Cancelled</option></select></div>
            <div><label class="block text-sm text-gray-400 mb-1">Year</label><input type="number" name="year" value="{{ old('year', $manga->year ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Rating (out of 10)</label><input type="number" step="0.1" name="rating" value="{{ old('rating', $manga->rating ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Score</label><input type="number" step="0.1" name="score" value="{{ old('score', $manga->score ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Author</label><input type="text" name="author" value="{{ old('author', $manga->author ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Artist</label><input type="text" name="artist" value="{{ old('artist', $manga->artist ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Publisher</label><input type="text" name="publisher" value="{{ old('publisher', $manga->publisher ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Source</label><input type="text" name="source" value="{{ old('source', $manga->source ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Alternative Titles</label><input type="text" name="alternative_titles" value="{{ old('alternative_titles', $manga->alternative_titles ?? '') }}" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700"></div>
        <div><label class="block text-sm text-gray-400 mb-1">Description</label><textarea name="description" rows="5" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700">{{ old('description', $manga->description ?? '') }}</textarea></div>
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm text-gray-400 mb-1">Thumbnail</label><input type="file" name="thumbnail" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white hover:file:bg-gray-700"></div>
            <div><label class="block text-sm text-gray-400 mb-1">Banner</label><input type="file" name="banner" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-800 file:text-white hover:file:bg-gray-700"></div>
            <div class="flex items-end"><label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="featured" value="1" @checked(old('featured', $manga->featured ?? false)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>Featured</span></label></div>
        </div>
        <div><label class="block text-sm text-gray-400 mb-1">Genres</label><div class="grid grid-cols-4 gap-2">@foreach($genres as $genre)<label class="flex items-center space-x-2 text-sm"><input type="checkbox" name="genres[]" value="{{ $genre->id }}" @checked(isset($manga) && $manga->genres->contains($genre->id)) class="rounded bg-gray-800 border-gray-700 text-purple-600"><span>{{ $genre->name }}</span></label>@endforeach</div></div>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">{{ isset($manga) ? 'Update' : 'Create' }}</button>
    </form>
</div>
@endsection
