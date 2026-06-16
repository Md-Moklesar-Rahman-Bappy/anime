@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold transition'
    : 'block w-full px-4 py-2 text-gray-400 hover:text-white hover:bg-[#1f2937] rounded-lg text-sm transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>