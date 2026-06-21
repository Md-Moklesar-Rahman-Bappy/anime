@props([
    'size' => null, // sm | lg | null
])

<button
    {{ $attributes->merge([
        'type'  => 'submit',
        'class' => 'btn-danger' . ($size ? ' btn-' . $size : ''),
    ]) }}
>
    {{ $slot }}
</button>