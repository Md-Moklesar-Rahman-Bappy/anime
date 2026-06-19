@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = match($maxWidth) {
    'sm' => '540px',
    'md' => '720px',
    'lg' => '960px',
    'xl' => '1140px',
    default => '720px',
};
@endphp

<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    class="position-fixed top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center px-3"
    style="z-index:1055;display:none"
>

    <!-- Overlay -->
    <div
        class="position-fixed top-0 start-0 end-0 bottom-0"
        style="background:rgba(0,0,0,0.7)"
        @click="show = false"
    ></div>

    <!-- Modal -->
    <div
        x-show="show"
        x-transition
        class="position-relative w-100 p-3"
        style="max-width:{{ $maxWidth }};background:#111827;border:1px solid #374151;border-radius:0.75rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5)"
    >
        {{ $slot }}
    </div>

</div>