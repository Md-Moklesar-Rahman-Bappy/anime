@props(['disabled' => false])

<input 
    @disabled($disabled) 
    {{ $attributes->merge([
        'class' => 'form-control'
    ]) }}
    style="background:#1f2937;border-color:#374151;color:#fff"
>