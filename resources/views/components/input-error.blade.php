@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge([
        'class' => 'mt-2 text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2 space-y-1'
    ]) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif