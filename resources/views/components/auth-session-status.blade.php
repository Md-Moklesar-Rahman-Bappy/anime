@props([
    'status' => null,
    'type'   => 'success', // success | error | info | warning
])

@if ($status)
    @php
        $styles = [
            'success' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
            'error'   => 'border-red-500/30 bg-red-500/10 text-red-400',
            'info'    => 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400',
            'warning' => 'border-amber-500/30 bg-amber-500/10 text-amber-400',
        ];

        $icons = [
            'success' => '✓',
            'error'   => '✗',
            'info'    => 'ℹ',
            'warning' => '⚠',
        ];

        $style = $styles[$type] ?? $styles['success'];
        $icon  = $icons[$type]  ?? $icons['success'];
    @endphp

    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 6000)"
        {{ $attributes->merge([
            'class' => 'rounded-lg border px-4 py-3 text-sm font-medium flex items-center gap-2 ' . $style
        ]) }}
    >
        <span class="font-bold">{{ $icon }}</span>
        <span>{{ $status }}</span>
    </div>
@endif