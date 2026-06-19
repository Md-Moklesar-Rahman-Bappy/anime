@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge([
        'class' => 'mt-2'
    ]) }} style="color:#f87171;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:0.5rem;padding:0.5rem 0.75rem">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif