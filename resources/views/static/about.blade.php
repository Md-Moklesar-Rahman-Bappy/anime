@extends('layouts.main')

@section('title', 'About Us')
@section('description', 'Learn about ' . config('app.name', 'AniKoto') . ' — your free anime and manga streaming destination.')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4 space-y-12">

    {{-- ─────── HERO ─────── --}}
    <section class="text-center">

        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/15 border border-indigo-500/30 mb-4">
            <i class="fas fa-film text-indigo-400 text-2xl"></i>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">
            About <span class="text-indigo-400">{{ config('app.name', 'AniKoto') }}</span>
        </h1>

        <p class="text-base text-gray-400 max-w-2xl mx-auto leading-relaxed">
            Your ultimate destination for streaming anime and reading manga online — anytime, anywhere.
        </p>
    </section>

    {{-- ─────── STORY ─────── --}}
    <section class="card p-6 sm:p-8 space-y-4 text-gray-400 leading-relaxed">

        <p>
            <span class="text-white font-semibold">{{ config('app.name', 'AniKoto') }}</span> was born from a simple idea —
            anime fans deserve a clean, fast, and free place to enjoy their favorite shows. We bring together a wide
            selection of anime series and movies, from timeless classics to the latest seasonal releases.
        </p>

        <p>
            Our mission is to create a seamless viewing experience for anime fans worldwide. With organized categories
            like genres, types, popularity, and trending shows, discovering new anime has never been easier.
        </p>

        <p>
            We continuously update our library with new episodes and chapters every day, so you never fall behind on
            your favorite series. No ads, no signup walls, no nonsense — just anime.
        </p>

        <p class="text-indigo-300 font-medium pt-2">
            🎬 Sit back, relax, and enjoy the world of anime with {{ config('app.name', 'AniKoto') }}.
        </p>
    </section>

    {{-- ─────── STATS ─────── --}}
    <section class="grid grid-cols-2 md:grid-cols-4 gap-4">

        @php
            $stats = [
                ['10K+',  'Anime Titles',  'fa-film',         'indigo'],
                ['200K+', 'Episodes',      'fa-play',         'pink'],
                ['5K+',   'Manga Series',  'fa-book',         'emerald'],
                ['1M+',   'Happy Users',   'fa-users',        'amber'],
            ];

            $colorMap = [
                'indigo'  => 'from-indigo-500/15 to-indigo-500/0 text-indigo-400 border-indigo-500/30',
                'pink'    => 'from-pink-500/15 to-pink-500/0 text-pink-400 border-pink-500/30',
                'emerald' => 'from-emerald-500/15 to-emerald-500/0 text-emerald-400 border-emerald-500/30',
                'amber'   => 'from-amber-500/15 to-amber-500/0 text-amber-400 border-amber-500/30',
            ];
        @endphp

        @foreach($stats as [$value, $label, $icon, $color])
            <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-5 text-center">
                <div class="absolute inset-0 bg-gradient-to-br {{ $colorMap[$color] }} pointer-events-none"></div>

                <div class="relative z-10">
                    <i class="fas {{ $icon }} text-2xl mb-2 {{ explode(' ', $colorMap[$color])[2] }}"></i>
                    <p class="text-2xl font-bold text-white">{{ $value }}</p>
                    <p class="text-xs uppercase tracking-wider text-gray-500 mt-1">{{ $label }}</p>
                </div>
            </div>
        @endforeach
    </section>

    {{-- ─────── FEATURES ─────── --}}
    <section>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">Why {{ config('app.name', 'AniKoto') }}?</h2>
            <p class="text-sm text-gray-400 mt-2">Built for fans, by fans.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            @php
                $features = [
                    [
                        'icon'  => 'fa-bolt',
                        'title' => 'Lightning Fast',
                        'desc'  => 'Optimized streaming so you spend less time loading and more time watching.',
                        'color' => 'amber',
                    ],
                    [
                        'icon'  => 'fa-ban',
                        'title' => 'No Ads',
                        'desc'  => 'No popups, no banners, no interruptions. Just pure anime experience.',
                        'color' => 'emerald',
                    ],
                    [
                        'icon'  => 'fa-gem',
                        'title' => 'HD Quality',
                        'desc'  => 'Watch in stunning 720p / 1080p quality with multiple server options.',
                        'color' => 'indigo',
                    ],
                    [
                        'icon'  => 'fa-mobile-screen-button',
                        'title' => 'Mobile Friendly',
                        'desc'  => 'Beautiful responsive design that works on phones, tablets, and TVs.',
                        'color' => 'pink',
                    ],
                    [
                        'icon'  => 'fa-clock-rotate-left',
                        'title' => 'Watch History',
                        'desc'  => 'Pick up right where you left off. Synced across all your devices.',
                        'color' => 'sky',
                    ],
                    [
                        'icon'  => 'fa-bookmark',
                        'title' => 'Personal Watchlist',
                        'desc'  => 'Save your favorites and get notified when new episodes drop.',
                        'color' => 'purple',
                    ],
                ];

                $iconColors = [
                    'amber'   => 'bg-amber-500/15 text-amber-400',
                    'emerald' => 'bg-emerald-500/15 text-emerald-400',
                    'indigo'  => 'bg-indigo-500/15 text-indigo-400',
                    'pink'    => 'bg-pink-500/15 text-pink-400',
                    'sky'     => 'bg-sky-500/15 text-sky-400',
                    'purple'  => 'bg-purple-500/15 text-purple-400',
                ];
            @endphp

            @foreach($features as $feature)
                <div class="card p-5 hover:border-gray-700 transition group">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3 {{ $iconColors[$feature['color']] }} group-hover:scale-110 transition-transform">
                        <i class="fas {{ $feature['icon'] }}"></i>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-1">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ─────── CTA ─────── --}}
    <section class="relative overflow-hidden rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-pink-600/20 p-8 sm:p-12 text-center">

        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">
            Ready to dive in?
        </h2>

        <p class="text-sm text-gray-300 max-w-xl mx-auto mb-6">
            Join thousands of anime fans already enjoying free, ad-free streaming.
            Create your free account in seconds.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            @guest
                {{ route('auth.register') }} class="btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i>
                    Create Free Account
                </a>
                {{ route('home') }} class="btn-outline btn-lg">
                    Browse Anime
                </a>
            @else
                {{ route('home') }} class="btn-primary btn-lg">
                    <i class="fas fa-play"></i>
                    Start Watching
                </a>
            @endguest
        </div>
    </section>

    {{-- ─────── CONTACT ─────── --}}
    <section class="text-center">
        <p class="text-sm text-gray-500">
            Got questions or feedback?
            {{ route('static.page', 'contact') }} class="text-indigo-400 hover:text-indigo-300 hover:underline transition">
                Get in touch with our team →
            </a>
        </p>
    </section>

</div>
@endsection