@extends('layouts.main')

@section('title', "{$manga->title} - Ch. " . rtrim(rtrim($chapter->number, '0'), '.'))

@section('content')
<div x-data="reader()" x-init="init()" 
     @keydown.left.window="prevPage()" 
     @keydown.right.window="nextPage()"
     style="min-height:100vh;background:#000;color:#fff">

    <div style="position:sticky;top:0;z-index:1050;background:rgba(10,10,15,0.95);border-bottom:1px solid #374151">
        <div class="container-fluid px-3 py-2 d-flex justify-content-between align-items-center" style="max-width:1280px">

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('manga.detail', $manga->slug) }}" style="color:#34d399;font-size:0.875rem">
                    ⬅ {{ $manga->title }}
                </a>

                <span style="color:#9ca3af;font-size:0.875rem">
                    Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">

                <span style="color:#9ca3af;font-size:0.875rem" x-text="currentPage + ' / ' + totalPages"></span>

                <select @change="goToChapter($event.target.value)"
                        style="background:#1f2937;font-size:0.875rem;padding:0.25rem 0.5rem;border-radius:0.25rem;border:1px solid #374151;color:#fff">
                    @foreach($allChapters as $ac)
                        <option value="{{ $ac->number }}" {{ $ac->id == $chapter->id ? 'selected' : '' }}>
                            Ch. {{ $ac->number }}
                        </option>
                    @endforeach
                </select>

            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-center" style="min-height:100vh">

        <template x-for="(page, index) in pages" :key="index">
            <img
                x-show="currentPage === index + 1"
                x-transition
                :src="page"
                :alt="'Page ' + (index + 1)"
                style="max-width:100%;height:auto;margin:0 auto;display:block"
                loading="lazy"
                draggable="false"
            >
        </template>

    </div>

    <div style="position:fixed;bottom:1.5rem;left:0;right:0;display:flex;justify-content:center;gap:1rem">

        <button @click="prevPage()" 
            style="background:#111827;border:1px solid #374151;color:#d1d5db;padding:0.5rem 1rem;border-radius:0.5rem;transition:background 0.2s"
            :disabled="currentPage <= 1">
            ⬅ Prev
        </button>

        <button @click="nextPage()" 
            style="background:#111827;border:1px solid #374151;color:#d1d5db;padding:0.5rem 1rem;border-radius:0.5rem;transition:background 0.2s"
            :disabled="currentPage >= totalPages">
            Next ➡
        </button>

    </div>

    <div style="background:#0a0a0f;border-top:1px solid #374151;padding:1.5rem 0">

        <div style="max-width:36rem;margin:0 auto;display:flex;justify-content:space-between">

            @if($prevChapter)
            <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$prevChapter->number]) }}" style="background:#111827;padding:0.5rem 1rem;border-radius:0.5rem;color:#d1d5db;text-decoration:none">
                ← Prev Chapter
            </a>
            @endif

            <a href="{{ route('manga.detail', $manga->slug) }}" style="background:#111827;padding:0.5rem 1rem;border-radius:0.5rem;color:#d1d5db;text-decoration:none">
                All Chapters
            </a>

            @if($nextChapter)
            <a href="{{ route('manga.read', ['slug'=>$manga->slug,'chapter'=>$nextChapter->number]) }}" style="background:#111827;padding:0.5rem 1rem;border-radius:0.5rem;color:#d1d5db;text-decoration:none">
                Next Chapter →
            </a>
            @endif

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