<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'btn'
]) }} style="background:#4f46e5;border-color:#4f46e5;color:#fff">
    {{ $slot }}
</button>