@extends('layouts.main')

@section('title', 'Manga')

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="visually-hidden">Manga Home</h1>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('manga.index') }}" class="btn d-inline-flex align-items-center gap-1" style="background:#059669;color:#fff;font-weight:600;font-size:0.875rem;box-shadow:0 4px 6px rgba(5,150,105,0.2)">
                <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Browse All Manga
            </a>
            <a href="{{ route('manga.updated') }}" class="btn d-inline-flex align-items-center gap-1" style="background:#1f2937;color:#d1d5db;font-weight:600;font-size:0.875rem">
                <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Recently Updated
            </a>
        </div>
    </div>

    @if($featured->count())
    <div
        x-data="{
            current: 0,
            autoplay: true,
            interval: null,
            progress: 0,
            total: {{ $featured->count() }},
            touchStartX: 0,
            init() { this.startAutoplay(); },
            startAutoplay() {
                if (this.interval) clearInterval(this.interval);
                this.progress = 0;
                this.interval = setInterval(() => {
                    this.progress += 1;
                    if (this.progress >= 100) { this.progress = 0; this.next(); }
                }, 50);
            },
            stopAutoplay() {
                if (this.interval) { clearInterval(this.interval); this.interval = null; }
                this.progress = 0;
            },
            next() {
                if (this.total <= 1) return;
                this.current = (this.current + 1) % this.total;
                if (this.autoplay) this.startAutoplay();
            },
            prev() {
                if (this.total <= 1) return;
                this.current = (this.current - 1 + this.total) % this.total;
                if (this.autoplay) this.startAutoplay();
            },
            goTo(index) {
                if (this.current === index) return;
                this.current = index;
                if (this.autoplay) this.startAutoplay();
            },
            toggleAutoplay() {
                this.autoplay = !this.autoplay;
                if (this.autoplay) this.startAutoplay();
                else this.stopAutoplay();
            },
            handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; },
            handleTouchEnd(e) {
                let diff = this.touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { if (diff > 0) this.next(); else this.prev(); }
            }
        }"
        x-init="init()"
        @keydown.left.window="prev()"
        @keydown.right.window="next()"
        @mouseenter="stopAutoplay()"
        @mouseleave="if (autoplay) startAutoplay()"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
        class="position-relative overflow-hidden mb-4"
        style="border-radius:0.75rem;height:400px;outline:none"
        tabindex="0" role="region" aria-label="Featured Manga"
    >
        @foreach($featured as $i => $manga)
        <div
            x-show="current === {{ $i }}"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 scale-105"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="position-absolute top-0 start-0 end-0 bottom-0"
            :aria-hidden="current !== {{ $i }}"
        >
            <div class="position-absolute top-0 start-0 end-0 bottom-0" style="background-size:cover;background-position:center;background-repeat:no-repeat;background-image:url('{{ $manga->banner_url ?? asset('fallback.jpg') }}')"></div>
            <div class="position-absolute top-0 start-0 end-0 bottom-0" style="background:linear-gradient(to top,#030712,rgba(3,7,18,0.6),transparent)"></div>
            <div class="position-absolute top-0 start-0 end-0 bottom-0" style="background:linear-gradient(to right,rgba(3,7,18,0.4),transparent)"></div>
            <div class="position-absolute bottom-0 start-0 end-0" style="padding:1.5rem 2.5rem">
                <h2 style="font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:0.5rem;text-shadow:0 2px 4px rgba(0,0,0,0.5)">{{ $manga->title }}</h2>
                <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:0.875rem;color:#d1d5db;margin-bottom:0.75rem">
                    <span style="padding:0.125rem 0.5rem;background:rgba(5,150,105,0.8);border-radius:0.25rem;font-size:0.75rem;font-weight:600">{{ $manga->type ?? 'Manga' }}</span>
                    <span>{{ $manga->year }}</span>
                    <span class="d-flex align-items-center">
                        <svg style="width:1rem;height:1rem;color:#eab308;margin-right:0.25rem" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ $manga->rating ?? 'N/A' }}
                    </span>
                    <span style="padding:0.125rem 0.5rem;border-radius:0.25rem;font-size:0.75rem;font-weight:600;{{ $manga->status === 'Ongoing' ? 'background:rgba(220,38,38,0.8)' : 'background:rgba(75,85,99,0.8)' }};color:#fff">{{ $manga->status ?? 'Unknown' }}</span>
                </div>
                <p style="color:#d1d5db;font-size:0.875rem;max-width:36rem;margin-bottom:1rem;text-shadow:0 1px 2px rgba(0,0,0,0.5)">{{ Str::limit($manga->description, 200) }}</p>
                <a href="{{ route('manga.detail', $manga->slug) }}" class="btn d-inline-flex align-items-center" style="background:#059669;color:#fff;font-weight:600;box-shadow:0 4px 6px rgba(5,150,105,0.3)">
                    <svg style="width:1.25rem;height:1.25rem;margin-right:0.5rem" fill="currentColor" viewBox="0 0 20 20"><path d="M9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                    Read Now
                </a>
            </div>
        </div>
        @endforeach

        @if($featured->count() > 1)
        <div class="position-absolute top-0 start-0 end-0 d-flex gap-1 p-2" style="z-index:30">
            @foreach($featured as $i => $manga)
            <div style="flex:1;height:0.25rem;background:rgba(255,255,255,0.2);border-radius:999px;overflow:hidden">
                <div style="height:100%;background:#10b981;border-radius:999px;transition:all 0.1s linear"
                     :style="'width: ' + (current === {{ $i }} ? progress + '%' : (current > {{ $i }} ? '100%' : '0%'))"></div>
            </div>
            @endforeach
        </div>

        <button @click="prev()" class="position-absolute top-50 start-0 translate-middle-y btn d-flex align-items-center justify-content-center" style="z-index:20;width:2.5rem;height:2.5rem;border-radius:50%;background:rgba(0,0,0,0.5);color:#fff;margin-left:0.75rem;opacity:0;transition:opacity 0.3s" aria-label="Previous slide">
            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" class="position-absolute top-50 end-0 translate-middle-y btn d-flex align-items-center justify-content-center" style="z-index:20;width:2.5rem;height:2.5rem;border-radius:50%;background:rgba(0,0,0,0.5);color:#fff;margin-right:0.75rem;opacity:0;transition:opacity 0.3s" aria-label="Next slide">
            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="position-absolute bottom-0 start-0 end-0 d-flex align-items-center justify-content-center gap-3" style="z-index:30;margin-bottom:1rem">
            <div class="d-flex align-items-center gap-1">
                @foreach($featured as $i => $manga)
                <button @click="goTo({{ $i }})" style="border-radius:50%;transition:all 0.3s;border:none;cursor:pointer"
                        :style="current === {{ $i }} ? 'width:1.5rem;height:0.625rem;background:#10b981;border-radius:999px' : 'width:0.625rem;height:0.625rem;background:rgba(255,255,255,0.5)'"
                        :aria-label="'Go to slide ' + ({{ $i }} + 1)"></button>
                @endforeach
            </div>
            <span class="d-none d-sm-inline" style="font-size:0.75rem;color:rgba(255,255,255,0.6);font-family:monospace" x-text="(current + 1).toString().padStart(2, '0') + ' / ' + total.toString().padStart(2, '0')"></span>
        </div>
        @endif
    </div>
    @endif

    <div class="row">
        <div class="col-lg-9 d-flex flex-column gap-4">
            <section>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 style="font-size:1.25rem;font-weight:600;color:#fff">Trending</h2>
                    <a href="{{ route('manga.trending') }}" style="color:#10b981;font-size:0.875rem">View All</a>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">
                    @foreach($trending as $manga)
                    <div class="col">
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="text-decoration-none">
                        <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">
                            <img src="{{ $manga->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s" loading="lazy">
                            <div style="position:absolute;top:0.5rem;left:0.5rem;background:rgba(17,24,39,0.8);color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem">{{ $manga->type ?? 'Manga' }}</div>
                            @if($manga->chapters_count > 0)
                            <div style="position:absolute;top:0.5rem;right:0.5rem;background:rgba(5,150,105,0.9);color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem;font-weight:700">Ch. {{ $manga->chapters_count }}</div>
                            @endif
                            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.8),transparent);opacity:0;transition:opacity 0.3s;display:flex;align-items:flex-end;padding:0.75rem">
                                <span style="color:#fff;font-size:0.875rem;font-weight:600">View Details</span>
                            </div>
                        </div>
                        <h3 style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $manga->title }}</h3>
                        <div class="d-flex align-items-center" style="font-size:0.75rem;color:#6b7280;margin-top:0.25rem">
                            <svg style="width:0.75rem;height:0.75rem;color:#eab308;margin-right:0.25rem" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $manga->rating ?? 'N/A' }}
                        </div>
                    </a>
                    </div>
                    @endforeach
                </div>
            </section>

            <section>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 style="font-size:1.25rem;font-weight:600;color:#fff">Recently Updated</h2>
                    <a href="{{ route('manga.updated') }}" style="color:#10b981;font-size:0.875rem">View All</a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($recentChapters as $chapter)
                    <a href="{{ route('manga.read', ['slug' => $chapter->manga->slug, 'chapter' => $chapter->number]) }}" class="d-flex align-items-center justify-content-between text-decoration-none" style="background:rgba(17,24,39,0.6);border-radius:0.5rem;padding:0.75rem 1rem;transition:background 0.3s">
                        <div class="d-flex align-items-center gap-2" style="min-width:0;flex:1">
                            <img src="{{ $chapter->manga->thumbnail_url }}" style="width:2.25rem;height:3rem;object-fit:cover;border-radius:0.25rem;flex-shrink:0" alt="">
                            <div style="min-width:0">
                                <p style="color:#d1d5db;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $chapter->manga->title }}</p>
                                <p style="font-size:0.75rem;color:#6b7280">
                                    @if($chapter->manga->type === 'Manhwa')
                                    <span style="color:#f97316;font-weight:500">Manhwa</span>
                                    @elseif($chapter->manga->type === 'Manhua')
                                    <span style="color:#60a5fa;font-weight:500">Manhua</span>
                                    @elseif($chapter->manga->type === 'One-shot')
                                    <span style="color:#f472b6;font-weight:500">One-shot</span>
                                    @elseif($chapter->manga->type === 'Doujinshi')
                                    <span style="color:#a78bfa;font-weight:500">Doujinshi</span>
                                    @else
                                    <span style="color:#34d399;font-weight:500">Manga</span>
                                    @endif
                                    <span style="color:#4b5563;margin:0 0.25rem">•</span>
                                    <span>Chap {{ $chapter->number }}</span>
                                    @if($chapter->title)
                                    <span style="color:#4b5563;margin:0 0.25rem">•</span>
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $chapter->title }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span style="font-size:0.75rem;color:#4b5563;flex-shrink:0;margin-left:1rem">{{ $chapter->created_at->diffForHumans() }}</span>
                    </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-lg-3 d-flex flex-column gap-3">
            <div x-data="{ tab: 'day' }" style="background:#111827;border-radius:0.5rem;padding:1rem">
                <h3 style="font-weight:700;font-size:1.125rem;margin-bottom:0.75rem">Most Viewed</h3>
                <div class="d-flex gap-1 mb-3">
                    <button @click="tab = 'day'" :style="tab === 'day' ? 'background:#059669;color:#fff' : 'background:#1f2937;color:#9ca3af'" style="padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:0.25rem;border:none;cursor:pointer;transition:background 0.2s">Day</button>
                    <button @click="tab = 'week'" :style="tab === 'week' ? 'background:#059669;color:#fff' : 'background:#1f2937;color:#9ca3af'" style="padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:0.25rem;border:none;cursor:pointer;transition:background 0.2s">Week</button>
                    <button @click="tab = 'month'" :style="tab === 'month' ? 'background:#059669;color:#fff' : 'background:#1f2937;color:#9ca3af'" style="padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:0.25rem;border:none;cursor:pointer;transition:background 0.2s">Month</button>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($mostViewed as $i => $manga)
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="d-flex align-items-center text-decoration-none" style="gap:0.75rem">
                        <span style="font-size:1.125rem;font-weight:700;color:#4b5563;width:1.5rem">{{ $i + 1 }}</span>
                        <img src="{{ $manga->thumbnail_url }}" style="width:2.5rem;height:3.5rem;object-fit:cover;border-radius:0.25rem" alt="">
                        <div style="flex:1;min-width:0">
                            <p style="color:#d1d5db;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $manga->title }}</p>
                            <div class="d-flex align-items-center" style="font-size:0.75rem;color:#6b7280">
                                <svg style="width:0.75rem;height:0.75rem;color:#eab308;margin-right:0.25rem" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $manga->rating ?? 'N/A' }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <div style="background:#111827;border-radius:0.5rem;padding:1rem">
                <h3 style="font-weight:700;font-size:1.125rem;margin-bottom:0.75rem">New Release</h3>
                <div class="d-flex flex-column gap-2">
                    @foreach($newManga->take(5) as $manga)
                    <a href="{{ route('manga.detail', $manga->slug) }}" class="d-flex align-items-center text-decoration-none" style="gap:0.75rem">
                        <img src="{{ $manga->thumbnail_url }}" style="width:2.5rem;height:3.5rem;object-fit:cover;border-radius:0.25rem" alt="">
                        <div style="flex:1;min-width:0">
                            <p style="color:#d1d5db;font-size:0.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $manga->title }}</p>
                            <p style="font-size:0.75rem;color:#6b7280">{{ $manga->year }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($newManga->count() > 5)
    <section class="mt-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 style="font-size:1.25rem;font-weight:600;color:#fff">Latest Manga</h2>
            <a href="{{ route('manga.newest') }}" style="color:#10b981;font-size:0.875rem">View All</a>
        </div>
        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 row-cols-lg-8 g-2">
            @foreach($newManga as $manga)
            <div class="col">
            <a href="{{ route('manga.detail', $manga->slug) }}" class="text-decoration-none">
                <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">
                    <img src="{{ $manga->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s" loading="lazy">
                </div>
                <p style="color:#9ca3af;font-size:0.75rem;margin-top:0.375rem;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.25">{{ $manga->title }}</p>
            </a>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection