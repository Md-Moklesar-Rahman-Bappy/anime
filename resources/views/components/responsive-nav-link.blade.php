@props(['active'])

@php
$classes = ($active ?? false)
    ? 'dropdown-item d-block w-100 px-4 py-2'
    : 'dropdown-item d-block w-100 px-4 py-2';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if($active ?? false) style="background:#4f46e5;color:#fff;border-radius:0.5rem" @else style="color:#9ca3af;border-radius:0.5rem" @endif>
    {{ $slot }}
</a>