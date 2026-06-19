@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'w-100 text-center'
    ]) }} style="color:#4ade80;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.2);border-radius:0.5rem;padding:0.5rem 1rem">
        {{ $status }}
    </div>
@endif