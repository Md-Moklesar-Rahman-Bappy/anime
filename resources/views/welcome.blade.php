@extends('layouts.main')

@section('title', 'Welcome — Watch Anime & Read Manga Free')
@section('description', 'Free anime streaming and manga reading. HD quality, daily updates, no ads. Join thousands of fans on ' . config('app.name', 'AniKoto') . '.')

@section('content')

{{-- ╔══════════════════════════════════════════╗
     ║         HERO SECTION                     ║
     ╚══════════════════════════════════════════╝ --}}
<section class="relative -mx-4 sm:-mx-6 lg:-mx-8 -mt-6 mb-20">

    <div class="relative min-h-[600px] sm:min-h-[700px] overflow-hidden">

        {{-- Decorative background --}}
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0a0a0f] via-[#1a0a2e] to-[#0a0a0f]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_25%,rgba(99,102,241,0.25),transparent_50%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_75%_75%,rgba(236,72,153,0.2),transparent_50%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_100%,rgba(168,85,247,0.15),transparent_50%)]"></div>

            {{-- Floating dots --}}
            <div class="absolute top-1/4 left-1/6 w-2 h-2 rounded-full bg-indigo-400 opacity-50 animate-pulse"></div>
            <div class="absolute top-1/2 right-1/4 w-1.5 h-1.5 rounded-full bg-pink-400 opacity-50 animate-pulse" style="animation-delay: 1s"></div>
            <div class="absolute bottom-1/4 left-1/3 w-2 h-2 rounded-full bg-purple-400 opacity-50 animate-pulse" style="animation-delay: 2s"></div>
        </div>

        {{-- Content --}}
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-20 sm:py-28 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-6 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 text-xs font-medium animate-fade-in">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                100% Free · No Ads · No Sign-up Required
            </div>

            {{-- Logo + brand --}}
            <div class="flex items-center justify-center gap-3 mb-6 animate-fade-in">
                <x-application-logo class="h-12 w-12 sm:h-16 sm:w-16" />
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight">
                    <span class="text-white">Ani</span><span class="text-gradient">{{ str_replace('Ani', '', config('app.name', 'AniKoto')) }}</span>
                </h1>
            </div>

            {{-- Tagline --}}
            <h2 class="text-xl sm:text-2xl lg:text-3xl text-white font-semibold mb-4 max-w-3xl mx-auto animate-fade-in">
                Watch Anime & Read Manga,
                <span class="text-gradient">Anywhere, Anytime</span>
            </h2>

            <p class="text-base text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed animate-fade-in">
                The ultimate streaming and reading platform built by fans, for fans.
                HD quality, daily updates, no popups, no nonsense.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3 mb-12 animate-fade-in">
                {{ route('home') }} class="btn-primary btn-lg group">
                    <i class="fas fa-play"></i>
                    Start Watching
                    <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                </a>

                @if(Route::has('manga.index'))
                    {{ route('manga.index') }} class="btn-success btn-lg group">
                        <i class="fas fa-book-open"></i>
                        Start Reading
                    </a>
                @endif

                @guest
                    {{ route('auth.register') }} class="btn-outline btn-lg">
                        Sign Up Free
                    </a>
                @endguest
            </div>

            {{-- Trust signals --}}
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs text-gray-400 animate-fade-in">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-400"></i>
                    <span>No registration required</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-400"></i>
                    <span>No ads, ever</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-400"></i>
                    <span>HD streaming</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-400"></i>
                    <span>Daily updates</span>
                </div>
            </div>

            {{-- Scroll prompt --}}
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-gray-500 animate-bounce">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>
</section>


{{-- ╔══════════════════════════════════════════╗
     ║         STATS SECTION                    ║
     ╚══════════════════════════════════════════╝ --}}
@php
    $stats = [
        ['10K+',  'Anime Titles',  'fa-film',  'indigo'],
        ['200K+', 'Episodes',      'fa-play',  'pink'],
        ['5K+',   'Manga Series',  'fa-book',  'emerald'],
        ['1M+',   'Happy Fans',    'fa-users', 'amber'],
    ];

    $statColors = [
        'indigo'  => 'from-indigo-500/15 to-indigo-500/0 text-indigo-400',
        'pink'    => 'from-pink-500/15 to-pink-500/0 text-pink-400',
        'emerald' => 'from-emerald-500/15 to-emerald-500/0 text-emerald-400',
        'amber'   => 'from-amber-500/15 to-amber-500/0 text-amber-400',
    ];
@endphp

<section class="max-w-6xl mx-auto mb-20">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        @foreach($stats as [$value, $label, $icon, $color])
            <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-[#111827] p-5 sm:p-6 text-center">
                <div class="absolute inset-0 bg-gradient-to-br {{ $statColors[$color] }} pointer-events-none"></div>
                <div class="relative">
                    <i class="fas {{ $icon }} text-2xl mb-2"></i>
                    <p class="text-2xl sm:text-3xl font-bold text-white">{{ $value }}</p>
                    <p class="text-xs uppercase tracking-wider text-gray-500 mt-1">{{ $label }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>


{{-- ╔══════════════════════════════════════════╗
     ║         FEATURES SECTION                 ║
     ╚══════════════════════════════════════════╝ --}}
<section class="max-w-6xl mx-auto mb-20 px-4">

    <div class="text-center mb-12">
        <p class="text-xs uppercase tracking-wider text-indigo-400 font-semibold mb-2">Features</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-white">
            Built for the perfect <span class="text-gradient">anime experience</span>
        </h2>
        <p class="text-sm text-gray-400 mt-3 max-w-2xl mx-auto">
            Every feature you need to enjoy anime and manga, beautifully crafted.
        </p>
    </div>

    @php
        $features = [
            ['fa-bolt',                'Lightning Fast',     'Optimized streaming so you spend less time loading and more time watching.',         'amber'],
            ['fa-ban',                 'Zero Ads',           'No popups, no banners, no interruptions. Just pure anime and manga.',                 'emerald'],
            ['fa-gem',                 'HD Quality',         'Watch in stunning 720p / 1080p with multiple server options for reliability.',        'indigo'],
            ['fa-mobile-screen-button','Mobile Friendly',    'Beautiful responsive design that works on phones, tablets, TVs.',                    'pink'],
            ['fa-clock-rotate-left',   'Watch History',      'Pick up right where you left off. Synced across all your devices.',                  'sky'],
            ['fa-bookmark',            'Personal Watchlist', 'Save your favorites, organize by category, never lose track again.',                 'purple'],
            ['fa-book-open',           'Smart Reader',       'Read manga vertically, horizontally, or 2-page spread with bookmark sync.',           'rose'],
            ['fa-comments',            'Active Community',   'Join discussions, share reactions, and connect with fellow anime fans.',              'teal'],
            ['fa-magnifying-glass',    'Powerful Search',    'Find any anime or manga instantly with filters for genre, year, status & more.',     'orange'],
        ];

        $featureColors = [
            'amber'   => 'bg-amber-500/15 text-amber-400',
            'emerald' => 'bg-emerald-500/15 text-emerald-400',
            'indigo'  => 'bg-indigo-500/15 text-indigo-400',
            'pink'    => 'bg-pink-500/15 text-pink-400',
            'sky'     => 'bg-sky-500/15 text-sky-400',
            'purple'  => 'bg-purple-500/15 text-purple-400',
            'rose'    => 'bg-rose-500/15 text-rose-400',
            'teal'    => 'bg-teal-500/15 text-teal-400',
            'orange'  => 'bg-orange-500/15 text-orange-400',
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($features as [$icon, $title, $desc, $color])
            <div class="card p-5 hover:border-gray-700 transition group">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 {{ $featureColors[$color] }} group-hover:scale-110 transition-transform">
                    <i class="fas {{ $icon }} text-lg"></i>
                </div>
                <h3 class="text-base font-semibold text-white mb-1.5">{{ $title }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>


{{-- ╔══════════════════════════════════════════╗
     ║         HOW IT WORKS                     ║
     ╚══════════════════════════════════════════╝ --}}
<section class="max-w-5xl mx-auto mb-20 px-4">

    <div class="text-center mb-12">
        <p class="text-xs uppercase tracking-wider text-pink-400 font-semibold mb-2">How it works</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-white">
            Watching anime made <span class="text-gradient">simple</span>
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">

        {{-- Connecting line (desktop only) --}}
        <div class="hidden md:block absolute top-8 left-1/4 right-1/4 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>

        @php
            $steps = [
                ['1', 'Browse', 'fa-compass', 'Explore our huge catalog of anime and manga, filtered by genre, year, and more.'],
                ['2', 'Watch', 'fa-play',     'Stream in HD with multiple server options. No registration needed to start.'],
                ['3', 'Track', 'fa-bookmark', 'Sign up free to save favorites, track progress, and sync across devices.'],
            ];
        @endphp

        @foreach($steps as [$num, $title, $icon, $desc])
            <div class="relative text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-2xl mb-4 shadow-lg shadow-indigo-500/30 relative z-10">
                    {{ $num }}
                </div>
                <h3 class="text-lg font-semibold text-white mb-2 flex items-center justify-center gap-2">
                    <i class="fas {{ $icon }} text-indigo-400 text-base"></i>
                    {{ $title }}
                </h3>
                <p class="text-sm text-gray-400 leading-relaxed max-w-xs mx-auto">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>


{{-- ╔══════════════════════════════════════════╗
     ║         BIG CTA                          ║
     ╚══════════════════════════════════════════╝ --}}
<section class="max-w-5xl mx-auto mb-20 px-4">

    <div class="relative overflow-hidden rounded-3xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/30 via-purple-600/20 to-pink-600/30 p-8 sm:p-14 text-center">

        {{-- Decorative blobs --}}
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm mb-4">
                <i class="fas fa-rocket text-white text-2xl"></i>
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-3">
                Ready to dive in?
            </h2>

            <p class="text-base sm:text-lg text-gray-200 max-w-xl mx-auto mb-8 leading-relaxed">
                Join thousands of anime fans already enjoying free, ad-free streaming.
                Get started in seconds — no credit card, no commitment.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3">
                @guest
                    {{ route('auth.register') }} class="btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Create Free Account
                    </a>
                @endguest

                {{ route('home') }} class="{{ auth()->check() ? 'btn-primary' : 'btn-outline' }} btn-lg">
                    <i class="fas fa-compass"></i> Browse Anime
                </a>
            </div>

            <p class="text-xs text-gray-400 mt-6">
                <i class="fas fa-shield-halved"></i>
                100% free forever · No credit card required
            </p>
        </div>
    </div>
</section>


{{-- ╔══════════════════════════════════════════╗
     ║         FAQ TEASER                       ║
     ╚══════════════════════════════════════════╝ --}}
<section class="max-w-3xl mx-auto mb-20 px-4 text-center">
    <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">Got questions?</p>
    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
        We have answers
    </h2>
    <p class="text-sm text-gray-400 mb-6">
        Check our FAQ for quick answers about streaming, accounts, and more.
    </p>
    {{ route('static.page', 'faq') }} class="btn-outline">
        <i class="fas fa-circle-question"></i> Visit FAQ
    </a>
</section>

@endsection