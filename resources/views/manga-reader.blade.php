@extends('layouts.main')

@section('title', "{$manga->title} - Ch. " . rtrim(rtrim($chapter->number, '0'), '.'))

@section('content')
<div x-data="reader()" x-init="init()" 
     @keydown.left.window="prevPage()" 
     @keydown.right.window="nextPage()"
     class="min-h-screen bg-black text-white">

    <!-- HEADER -->
    <div class="sticky top-0 z-50 bg-[#0a0a0f]/95 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">

            <div class="flex items-center gap-4">
                <a href="{{ route('manga.detail', $manga->slug) }}" class="text-emerald-400 hover:text-emerald-300 text-sm">
                    ⬅ {{ $manga->title }}
                </a>

                <span class="text-gray-400 text-sm">
                    Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}
                </span>
            </div>

            <div class="flex items-center gap-3">

                <span class="text-gray-400 text-sm" x-text="currentPage + ' / ' + totalPages"></span>

                <select @change="goToChapter($event.target.value)"
                        class="bg-[#1f2937] text-sm px-2 py-1 rounded border border-gray-700">
                    @foreach($allChapters as $ac)
                        <option value="{{ $ac->number }}" {{ $ac->id == $chapter->id ? 'selected' : '' }}>
                            Ch. {{ $ac->number }}
                        </option>
                    @endforeach
                </select>

            </div>
        </div>
    </div>

    <!-- READER -->
    <div class="flex justify-center items-center min-h-screen">

        <template x-for="(page, index) in pages" :key="index">
            <img
                x-show="currentPage === index + 1"
                x-transition
                :src="page"
                :alt="'Page ' + (index + 1)"
                class="reader-img"
                loading="lazy"
                draggable="false"
            >
        </template>

    </div>

    <!-- NAVIGATION -->
    <div class="fixed bottom-6 left-0 right-0 flex justify-center gap-4">

        <button @click="prevPage()" 
            class="nav-btn"
            :disabled="currentPage <= 1">
            ⬅ Prev
        </button>

        <button @click="nextPage()" 
            class="nav-btn"
            :disabled="currentPage >= totalPages">
            Next ➡
        </button>

    </div>

    <!-- CHAPTER NAV -->
    <div class="bg-[#0a0a0f] border-t border-gray-800 py-6">

        <div class="max-w-xl mx-auto flex justify-between">

            @if($prevChapter)
            <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$prevChapter->number]) }}" class="nav-bottom">
                ← Prev Chapter
            </a>
            @endif

            <a href="{{ route('manga.detail', $manga->slug) }}" class="nav-bottom">
                All Chapters
            </a>

            @if($nextChapter)
            <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$nextChapter->number]) }}" class="nav-bottom">
                Next Chapter →
            </a>
            @endif

        </div>

    </div>

</div>

<style>
.reader-img {
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    display: block;
}

.nav-btn {
    @apply bg-[#111827] border border-gray-800 text-gray-300 px-4 py-2 rounded-lg hover:bg-emerald-600 transition;
}

.nav-bottom {
    @apply bg-[#111827] px-4 py-2 rounded-lg text-gray-300 hover:bg-emerald-600;
}
</style>

<script>
function reader() {
    return {
        currentPage: {{ $bookmark->page_number ?? 1 }},
        totalPages: {{ $chapter->pages->count() }},

        pages: [
            @foreach($chapter->pages as $page)
                '{{ str_starts_with($page->image_path, "http") 
                    ? $page->image_path 
                    : asset("storage/".$page->image_path) }}',
            @endforeach
        ],

        init() {
            if (this.currentPage > this.totalPages) {
                this.currentPage = 1;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.save();
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.save();
            }
        },

        goToChapter(ch) {
            window.location.href = `{{ route('manga.read', ['slug'=>$manga->slug]) }}/${ch}`;
        },

        save() {
            fetch('/manga/bookmark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    chapter_id: {{ $chapter->id }},
                    page_number: this.currentPage
                })
            });
        }
    }
}
</script>
@endsection