@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display: none;"
>

    <!-- Overlay -->
    <div
        class="fixed inset-0 bg-black/70 backdrop-blur-sm"
        @click="show = false"
    ></div>

    <!-- Modal -->
    <div
        x-show="show"
        x-transition
        class="relative w-full {{ $maxWidth }} bg-[#111827] border border-gray-800 rounded-2xl shadow-2xl p-6"
    >
        {{ $slot }}
    </div>

</div>