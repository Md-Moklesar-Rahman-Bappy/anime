@extends('layouts.main')

@section('title', $anime->title . ' - Episode ' . $episode->number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- LEFT: PLAYER -->
        <div class="lg:col-span-3 space-y-4">

            <!-- Video Player -->
            <div class="bg-black rounded-xl overflow-hidden aspect-video flex items-center justify-center">

                @if($episode->video_url)
                    <iframe
                        src="{{ $episode->video_url }}"
                        class="w-full h-full"
                        frameborder="0"
                        allowfullscreen>
                    </iframe>
                @else
                    <div class="text-gray-500">No video available</div>
                @endif

            </div>

            <!-- Controls -->
            <div class="flex justify-between items-center">

                <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $episode->number - 1]) }}"
                   class="btn-nav"
                   @if($episode->number <= 1) style="opacity:.4;pointer-events:none" @endif>
                   ⬅ Prev
                </a>

                <h1 class="text-lg text-white font-semibold">
                    {{ $anime->title }} — Episode {{ $episode->number }}
                </h1>

                <a href="{{ route('watch', ['slug' => $anime->slug, 'ep' => $episode->number + 1]) }}"
                   class="btn-nav"
                   @if($episode->number >= $anime->episodes_count) style="opacity:.4;pointer-events:none" @endif>
                   Next ➡
                </a>

            </div>

            <!-- Server Switch -->
            <div class="flex gap-2 flex-wrap">
                @foreach($episode->servers ?? [] as $server)
                    <button class="server-btn">
                        {{ $server->name }}
                    </button>
                @endforeach
            </div>

            <!-- Description -->
            <div class="bg-[#111827] border border-gray-800 rounded-xl p-4">
                <p class="text-gray-300 text-sm">
                    {{ $episode->description ?? $anime->description }}
                </p>
            </div>

            <!-- Comments -->
            <div class="bg-[#111827] border border-gray-800 rounded-xl p-4 space-y-4">
                <h2 class="text-white font-semibold">Comments</h2>

                @auth
                <form method="POST" action="{{ route('comments.store') }}">
                    @csrf
                    <textarea name="comment"
                              placeholder="Write your comment..."
                              class="w-full bg-[#1f2937] text-white p-3 rounded-lg text-sm"></textarea>

                    <button class="mt-2 bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded">
                        Post
                    </button>
                </form>
                @endauth

                @foreach($comments as $comment)
                <div class="flex gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}"
                         class="w-8 h-8 rounded-full">

                    <div>
                        <p class="text-sm text-white">{{ $comment->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $comment->body }}</p>
                    </div>
                </div>
                @endforeach

            </div>

        </div>

        <!-- RIGHT: EPISODES -->
        <div class="space-y-4">

            <div class="bg-[#111827] border border-gray-800 rounded-xl p-4">

                <h2 class="text-white font-semibold mb-3">Episodes</h2>

                <div class="max-h-[500px] overflow-y-auto space-y-2">

                    @foreach($anime->episodes as $ep)
                    <a href="{{ route('watch', ['slug'=>$anime->slug,'ep'=>$ep->number]) }}"
                       class="episode-item {{ $ep->number == $episode->number ? 'active' : '' }}">

                        <span>Ep {{ $ep->number }}</span>

                        <span class="text-xs text-gray-500">
                            {{ $ep->title ?? '' }}
                        </span>

                    </a>
                    @endforeach

                </div>
            </div>

        </div>

    </div>

</div>

<style>
.btn-nav {
    @apply bg-[#111827] border border-gray-800 text-gray-300 px-4 py-2 rounded-lg hover:bg-[#1f2937];
}

.server-btn {
    @apply bg-[#1f2937] text-sm px-3 py-1 rounded-lg hover:bg-indigo-600 text-gray-300;
}

.episode-item {
    @apply block p-2 rounded-lg bg-[#1f2937] text-gray-300 hover:bg-[#2b3545];
}

.episode-item.active {
    @apply bg-indigo-600 text-white;
}
</style>
@endsection