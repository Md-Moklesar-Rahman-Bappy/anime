<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'btn'
]) }} style="background:#1f2937;border-color:#374151;color:#d1d5db">
    {{ $slot }}
</button>