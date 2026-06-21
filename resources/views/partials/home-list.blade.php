@props([
    'title'     => '',
    'icon'      => 'fa-list',
    'iconColor' => 'text-gray-400',
    'items'     => collect(),
    'link'      => '#',
])

<div>
    <div class="section-title">
        <h2 class="text-base !text-sm uppercase tracking-wider">
            <i class="fas {{ $icon }} {{ $iconColor }}"></i>
            {{ $title }}
        </h2>
        {{ $link }}>
    </div>

    <div class="card divide-y divide-gray-800">
        @forelse($items->take(5) as $anime)
            {{ route('anime.detail', $anime->slug) }} class="flex gap-3 p-3 group hover:bg-white/[0.02] transition">

                <div class="aspect-poster w-10 sm:w-12 rounded overflow-hidden bg-gray-900 shrink-0">
                    {{ $anime->thumbnail_url ?? $anime->poster_url }}
                         class="w-full h-full object-cover group-hover:scale-105 transition"
                         alt="{{ $anime->title }}"
                         loading="lazy"
                    >
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-300 group-hover:text-white clamp-2 leading-snug">
                        {{ $anime->title }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                        @if($anime->type ?? null)
                            <span class="uppercase">{{ $anime->type }}</span>
                        @endif
                        @if($anime->episodes_count ?? null)
                            <span>{{ $anime->episodes_count }} EP</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="p-6 text-center text-xs text-gray-500">
                No items yet.
            </div>
        @endforelse
    </div>
</div>