@extends('admin.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">{{ $data['anime_title'] }} — Episodes</h1>
        <a href="{{ route('admin.scrapers.search') }}" class="text-purple-500 hover:text-purple-400 text-sm">Back to search</a>
    </div>

    <div class="bg-gray-900 rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-400">Source: <span class="text-white">{{ $scraper->name() }}</span></p>
        <p class="text-sm text-gray-400">Episodes found: <span class="text-white">{{ count($episodes) }}</span></p>
    </div>

    <form action="{{ route('admin.scrapers.import') }}" method="POST">
        @csrf
        <input type="hidden" name="scraper" value="{{ get_class($scraper) }}">
        <input type="hidden" name="anime_id" value="{{ $data['anime_id'] }}">
        <input type="hidden" name="anime_title" value="{{ $data['anime_title'] }}">
        @if($data['local_anime_id'] ?? false)
            <input type="hidden" name="local_anime_id" value="{{ $data['local_anime_id'] }}">
        @endif

        <div class="bg-gray-900 rounded-lg p-4 mb-6">
            <label class="flex items-center space-x-2 mb-4">
                <input type="checkbox" id="selectAll" onclick="document.querySelectorAll('.ep-checkbox').forEach(c => c.checked = this.checked)" class="rounded bg-gray-800 border-gray-700 text-purple-600">
                <span class="text-sm font-semibold">Select All</span>
            </label>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($episodes as $ep)
                    <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-800 cursor-pointer">
                        <input type="checkbox" name="episodes[{{ $loop->index }}][id]" value="{{ $ep['id'] }}" class="ep-checkbox rounded bg-gray-800 border-gray-700 text-purple-600">
                        <input type="hidden" name="episodes[{{ $loop->index }}][number]" value="{{ $ep['number'] }}">
                        <span class="text-sm">Episode {{ $ep['number'] }}</span>
                        @if($ep['title'] ?? false)
                            <span class="text-xs text-gray-500">— {{ $ep['title'] }}</span>
                        @endif
                    </label>
                @empty
                    <p class="text-gray-500 text-sm">No episodes found.</p>
                @endforelse
            </div>
        </div>

        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">
            Import Selected Episodes
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ids = document.querySelectorAll('input[name^="episodes["][name$="[id]"]');
    ids.forEach(function(input, index) {
        var numInput = document.querySelector('input[name="episodes[' + index + '][number]"]');
        if (numInput && input.checked) {
            numInput.disabled = false;
        }
    });
});
</script>
@endsection
