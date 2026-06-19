@props(['value'])

<label {{ $attributes->merge([
    'class' => 'form-label'
]) }} style="color:#d1d5db">
    {{ $value ?? $slot }}
</label>