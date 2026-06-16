@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div 
        x-show="open"
        x-transition
        class="absolute z-50 mt-2 {{ $width }} {{ $alignmentClasses }}"
        style="display: none;"
    >
        <div class="bg-[#111827] border border-gray-800 rounded-lg shadow-xl py-1">
            {{ $content }}
        </div>
    </div>
</div>