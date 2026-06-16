<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4 py-2 bg-[#1f2937] hover:bg-[#374151] text-gray-300 hover:text-white rounded-lg text-sm font-medium transition border border-gray-700'
]) }}>
    {{ $slot }}
</button>