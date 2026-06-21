@props([
    'active' => false,
    'icon'   => null,
])

@php
    $classes = $active
        ? 'flex items-center gap-2 w-full px-4 py-2 text-sm text-white bg-indigo-600/20 border-l-2 border-indigo-500 transition'
        : 'flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="fas {{ $icon }} text-xs w-4 text-center text-gray-500"></i>
    @endif

    <span>{{ $slot }}</span>
</a>