@extends('layouts.main')

@section('title', "{$manga->title} - Ch. " . rtrim(rtrim($chapter->number, '0'), '.'))

@push('styles')
<style>
.reader-page-img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
    user-select: none;
    -webkit-user-drag: none;
}
</style>
@endpush

@section('content')
<div x-data="reader()" x-init="init()" @keydown.left.window="prevPage()" @keydown.right.window="nextPage()" class="min-h-screen bg-black">
    <div class="sticky top-0 z-50 bg-gray-950/95 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('manga.detail', $manga->slug) }}" class="text-purple-500 hover:text-purple-400 text-sm">
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ $manga->title }}
                </a>
                <span class="text-gray-400 text-sm">Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}</span>
                @if($chapter->title)<span class="text-gray-500 text-sm hidden md:inline">- {{ $chapter->title }}</span>@endif
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-gray-400 text-sm" x-text="currentPage + ' / ' + totalPages"></span>

                <select @change="goToChapter($event.target.value)" class="bg-gray-800 text-white text-sm rounded-lg px-3 py-1.5 border border-gray-700">
                    @foreach($allChapters as $ac)
                    <option value="{{ $ac->number }}" {{ $ac->id == $chapter->id ? 'selected' : '' }}>
                        Ch. {{ rtrim(rtrim($ac->number, '0'), '.') }}@if($ac->title) - {{ $ac->title }}@endif
                    </option>
                    @endforeach
                </select>

                @if($prevChapter)
                <a href="{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $prevChapter->number]) }}" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @if($nextChapter)
                <a href="{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $nextChapter->number]) }}" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="relative" @click="handleClick($event)">
        <div class="flex items-center justify-center min-h-screen">
            <template x-for="(page, index) in pages" :key="index">
                <img
                    x-show="currentPage === index + 1"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    :src="page"
                    :alt="'Page ' + (index + 1)"
                    class="reader-page-img"
                    @load="loaded()"
                    @error="errored(index)"
                >
            </template>

            <div x-show="!pages.length" class="text-gray-500 text-center py-20">
                No pages found for this chapter.
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-40 flex justify-between px-4 pb-4 pointer-events-none">
            <button @click="prevPage()" class="pointer-events-auto bg-gray-900/80 hover:bg-purple-600 text-white p-3 rounded-full transition opacity-0 hover:opacity-100 focus:opacity-100" :class="currentPage > 1 ? 'opacity-60' : 'opacity-0'" :disabled="currentPage <= 1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="nextPage()" class="pointer-events-auto bg-gray-900/80 hover:bg-purple-600 text-white p-3 rounded-full transition opacity-0 hover:opacity-100 focus:opacity-100" :class="currentPage < totalPages ? 'opacity-60' : 'opacity-0'" :disabled="currentPage >= totalPages">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="fixed left-0 top-1/2 -translate-y-1/2 z-30 w-1/3 h-full cursor-pointer" @click="prevPage()"></div>
        <div class="fixed right-0 top-1/2 -translate-y-1/2 z-30 w-1/3 h-full cursor-pointer" @click="nextPage()"></div>
    </div>

    <div class="bg-gray-950 border-t border-gray-800 py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="flex items-center justify-center space-x-4 mb-4">
                @if($prevChapter)
                <a href="{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $prevChapter->number]) }}" class="bg-gray-800 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    ← Ch. {{ rtrim(rtrim($prevChapter->number, '0'), '.') }}
                </a>
                @endif
                <a href="{{ route('manga.detail', $manga->slug) }}" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    All Chapters
                </a>
                @if($nextChapter)
                <a href="{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => $nextChapter->number]) }}" class="bg-gray-800 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ch. {{ rtrim(rtrim($nextChapter->number, '0'), '.') }} →
                </a>
                @endif
            </div>

            @auth
            <form action="{{ route('manga.comments.store') }}" method="POST" class="mb-6">
                @csrf
                <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                <textarea name="body" rows="3" class="w-full bg-gray-800 text-white rounded-lg px-4 py-2 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Leave a comment..." required></textarea>
                <button type="submit" class="mt-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">Post Comment</button>
            </form>
            @endauth

            <div class="space-y-3">
                @foreach($comments as $comment)
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="flex items-center space-x-2 mb-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=7c3aed&color=fff" class="w-6 h-6 rounded-full" alt="">
                        <span class="text-sm text-gray-300">{{ $comment->user->name }}</span>
                        <span class="text-xs text-gray-600">{{ $comment->created_at->diffForHumans() }}</span>
                        @auth
                        @if(auth()->user()->isSuperAdmin())
                        <form action="{{ route('manga.comments.destroy', $comment) }}" method="POST" class="ml-auto" onsubmit="return confirm('Delete this comment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-400">Delete</button>
                        </form>
                        @endif
                        @endauth
                    </div>
                    <p class="text-sm text-gray-400">{{ $comment->body }}</p>
                </div>
                @endforeach
                @if($comments->hasPages())
                <div class="mt-4">{{ $comments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function reader() {
    return {
        currentPage: {{ $bookmark->page_number ?? 1 }},
        totalPages: {{ $chapter->pages->count() }},
        pages: [
            @foreach($chapter->pages as $page)
                @if(str_starts_with($page->image_path, 'http'))
                    '{{ $page->image_path }}',
                @else
                    '{{ asset('storage/'.$page->image_path) }}',
                @endif
            @endforeach
        ],
        loading: false,

        init() {
            if (this.currentPage > this.totalPages) this.currentPage = 1;
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.saveProgress();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.saveProgress();
            }
        },

        goToChapter(number) {
            const url = '{{ route('manga.read', ['slug' => $manga->slug, 'chapter' => '']) }}/' + number;
            window.location.href = url;
        },

        handleClick(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            if (x < width / 3) {
                this.prevPage();
            } else if (x > (width * 2) / 3) {
                this.nextPage();
            }
        },

        loaded() {
            this.loading = false;
        },

        errored(index) {
            this.pages[index] = 'https://via.placeholder.com/800x1200/1a1a2e/7c3aed?text=Page+Not+Found';
        },

        saveProgress() {
            @auth
            fetch('/manga/bookmark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ chapter_id: {{ $chapter->id }}, page_number: this.currentPage })
            });
            @endauth
        }
    }
}
</script>
@endsection
