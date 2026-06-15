<a {{ $attributes->merge([
    'class' => 'block w-full px-4 py-2 text-sm text-gray-300 hover:bg-[#1f2937] hover:text-white rounded-md transition'
]) }}>
    {{ $slot }}
</a>
