@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge([
        'class' => 'w-full rounded-lg border border-gray-700 bg-[#1f2937] px-3 py-2 text-white placeholder-gray-500
                    focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition'
    ]) }}
>