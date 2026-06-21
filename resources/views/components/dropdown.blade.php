@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1',
])

@php
    $alignmentClasses = match ($align) {
        'left'   => 'origin-top-left left-0',
        'top'    => 'origin-top',
        'right'  => 'origin-top-right right-0',
        default  => 'origin-top-right right-0',
    };

    $widthClasses = match ((string) $width) {
        '32'  => 'w-32',
        '40'  => 'w-40',
        '48'  => 'w-48',
        '56'  => 'w-56',
        '64'  => 'w-64',
        '72'  => 'w-72',
        '80'  => 'w-80',
        '96'  => 'w-96',
        default => 'w-48',
    };
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    {{-- TRIGGER --}}
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    {{-- DROPDOWN PANEL --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClasses }} {{ $alignmentClasses }} rounded-xl bg-[#0f111a] border border-gray-800 shadow-xl overflow-hidden"
        @click="open = false"
    >
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>