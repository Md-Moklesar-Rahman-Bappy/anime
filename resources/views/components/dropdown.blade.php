@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = match ($align) {
    'left' => 'start-0',
    'top' => '',
    default => 'end-0',
};
@endphp

<div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div 
        x-show="open"
        x-transition
        class="position-absolute z-3 mt-2 {{ $alignmentClasses }}"
        style="display:none;min-width:12rem"
    >
        <div style="background:#111827;border:1px solid #374151;border-radius:0.5rem;box-shadow:0 20px 25px -5px rgba(0,0,0,0.3);padding:0.25rem 0">
            {{ $content }}
        </div>
    </div>
</div>