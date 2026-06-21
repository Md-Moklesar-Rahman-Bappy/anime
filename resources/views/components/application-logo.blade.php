<svg
    {{ $attributes->merge(['class' => 'text-indigo-500']) }}
    viewBox="0 0 64 64"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    style="width:2.5rem;height:2.5rem"
>
    {{-- Outer gradient ring --}}
    <defs>
        <linearGradient id="logo-grad" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#6366f1" />
            <stop offset="100%" stop-color="#a855f7" />
        </linearGradient>
    </defs>

    {{-- Rounded square background --}}
    <rect width="64" height="64" rx="16" fill="url(#logo-grad)" />

    {{-- Play triangle --}}
    <path d="M26 20L46 32L26 44V20Z" fill="white" />
</svg>