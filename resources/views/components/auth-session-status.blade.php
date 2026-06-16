@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'w-full px-4 py-2 rounded-lg text-sm font-medium bg-green-500/10 text-green-400 border border-green-500/20 text-center'
    ]) }}>
        {{ $status }}
    </div>
@endif