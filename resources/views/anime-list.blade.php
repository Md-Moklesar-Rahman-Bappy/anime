@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fw-semibold" style="color:#fff;font-size:1.5rem">
                {{ $title }}
            </h1>
            <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
                {{ $animeList->total() }} results
            </p>
        </div>
    </div>

    <div x-data="{ filterOpen:false }" class="d-flex gap-3">

        <button @click="filterOpen = true"
            class="d-lg-none position-fixed bottom-0 end-0 mb-4 me-3 btn d-flex align-items-center justify-content-center"
            style="width:3rem;height:3rem;border-radius:50%;background:#4f46e5;color:#fff;z-index:1050;box-shadow:0 4px 6px rgba(0,0,0,0.3)">
            ⚙
        </button>

        <div x-show="filterOpen" x-cloak
             class="position-fixed top-0 start-0 end-0 bottom-0 d-lg-none"
             style="background:rgba(0,0,0,0.6);z-index:1040"
             @click="filterOpen=false"></div>

        <aside style="width:18rem;flex-shrink:0" class="d-none d-lg-block"
               :class="filterOpen ? 'd-block position-fixed top-0 start-0 bottom-0 w-100' : ''"
               :style="filterOpen ? 'z-index:1050;overflow-y:auto' : ''">

            <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.25rem;height:100%;overflow-y:auto">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold" style="color:#d1d5db;font-size:0.875rem">Filters</span>
                    <button @click="filterOpen=false" class="d-lg-none btn btn-sm" style="background:none;border:none;color:#fff">✕</button>
                </div>

                <form action="{{ route('filter') }}" method="GET">

                    <div style="border-bottom:1px solid #374151;padding-bottom:1rem;margin-bottom:1rem">
                        <div style="font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:0.75rem">Genres</div>

                        <div class="row row-cols-3 g-1">
                            @foreach($genres as $genre)
                            <div class="col">
                            <label style="display:inline-block;padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:0.5rem;border:1px solid #374151;cursor:pointer;{{ in_array($genre->slug, (array)request('genres')) ? 'background:#4f46e5;border-color:#6366f1;color:#fff' : 'color:#9ca3af' }}"
                                   class="{{ in_array($genre->slug, (array)request('genres')) ? '' : '' }}">
                                <input type="checkbox" name="genres[]" value="{{ $genre->slug }}"
                                       {{ in_array($genre->slug, (array)request('genres')) ? 'checked' : '' }}
                                       class="d-none" onchange="this.form.submit()">
                                {{ $genre->name }}
                            </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="border-bottom:1px solid #374151;padding-bottom:1rem;margin-bottom:1rem">
                        <div style="font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:0.75rem">Type</div>

                        <div class="d-flex flex-wrap gap-1">
                            @foreach(['TV','Movie','OVA','ONA'] as $type)
                            <label style="display:inline-block;padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:0.5rem;border:1px solid #374151;cursor:pointer;{{ request('type') === $type ? 'background:#4f46e5;border-color:#6366f1;color:#fff' : 'color:#9ca3af' }}">
                                <input type="radio" name="type" value="{{ $type }}"
                                       {{ request('type') === $type ? 'checked' : '' }}
                                       class="d-none" onchange="this.form.submit()">
                                {{ $type }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="border-bottom:1px solid #374151;padding-bottom:1rem;margin-bottom:1rem">
                        <div style="font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:0.75rem">Sort</div>

                        <select name="sort"
                            onchange="this.form.submit()"
                            class="form-select"
                            style="background:#1f2937;border-color:#374151;color:#fff;font-size:0.875rem">
                            <option value="">Latest</option>
                            <option value="views" @selected(request('sort')==='views')>Popular</option>
                            <option value="score" @selected(request('sort')==='score')>Score</option>
                        </select>
                    </div>

                </form>
            </div>
        </aside>

        <div style="flex:1;min-width:0">

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">

                @forelse($animeList as $anime)

                <div class="col">
                <a href="{{ route('anime.detail', $anime->slug) }}" class="text-decoration-none group">

                    <div style="position:relative;border-radius:0.75rem;overflow:hidden;background:#111827;aspect-ratio:2/3">

                        {{ $anime->thumbnail_url }}

                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);opacity:0;color:#fff;font-size:0.875rem;transition:opacity 0.3s;z-index:2">
                            ▶ View
                        </div>

                        <span style="position:absolute;top:0.5rem;left:0.5rem;background:rgba(0,0,0,0.7);color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem;z-index:1">
                            {{ $anime->type }}
                        </span>

                        @if($anime->episodes_count)
                        <span style="position:absolute;top:0.5rem;right:0.5rem;background:#4f46e5;color:#fff;font-size:0.75rem;padding:0.25rem 0.5rem;border-radius:0.25rem;z-index:1">
                            {{ $anime->episodes_count }}
                        </span>
                        @endif

                    </div>

                    <h3 style="color:#d1d5db;font-size:0.875rem;margin-top:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $anime->title }}
                    </h3>

                    <div style="color:#6b7280;font-size:0.75rem;margin-top:0.25rem">
                        ⭐ {{ $anime->score ?? 'N/A' }}
                    </div>

                </a>
                </div>

                @empty
                <div class="col-12 text-center py-5" style="color:#6b7280">
                    No anime found
                </div>
                @endforelse

            </div>

            <div class="mt-4">
                {{ $animeList->links() }}
            </div>

        </div>

    </div>
</div>
@endsection
