@props([
    'href',
    'active' => false,
    'icon'   => null,
])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition
          {{ $active
              ? 'bg-indigo-600/90 text-white shadow'
              : 'text-gray-400 hover:text-white hover:bg-gray-800/70' }}">

    @if($icon)
        <i class="fas {{ $icon }} text-xs w-4 text-center"></i>
    @endif

    <span>{{ $slot }}</span>
</a>