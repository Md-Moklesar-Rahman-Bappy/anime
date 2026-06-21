@extends('layouts.main')

@section('title', 'FAQ')
@section('description', 'Frequently asked questions about ' . config('app.name', 'AniKoto') . ' — find quick answers about streaming, accounts, and more.')

@php
    $brand = config('app.name', 'AniKoto');

    $faqs = [
        [
            'category' => 'General',
            'icon'     => 'fa-circle-info',
            'color'    => 'indigo',
            'items'    => [
                ['q' => "What is {$brand}?",
                 'a' => "{$brand} is a free anime streaming and manga reading platform. We bring together a huge selection of series, movies, and chapters — from timeless classics to the latest releases — all in one clean, ad-free experience."],

                ['q' => "Is {$brand} free?",
                 'a' => "Yes! {$brand} is completely free to use. No subscription, no premium tier, no hidden costs. We're built by anime fans, for anime fans."],

                ['q' => "Do I need an account to watch?",
                 'a' => "You can browse and watch most content without an account. However, an account unlocks features like watchlists, watch history sync, comments, ratings, and personalized recommendations."],

                ['q' => "Is {$brand} legal?",
                 'a' => "{$brand} does not host any content on our servers. We only index and link to media hosted on third-party platforms. For copyright concerns, please see our DMCA Policy."],
            ],
        ],
        [
            'category' => 'Streaming',
            'icon'     => 'fa-play',
            'color'    => 'pink',
            'items'    => [
                ['q' => "Why is the video buffering or not playing?",
                 'a' => "Try switching to a different server or quality from the player. If the issue persists, clear your browser cache, disable ad-blockers (we don't use ads but they can block our player), or try a different browser."],

                ['q' => "What video qualities are available?",
                 'a' => "Most episodes are available in 360p, 480p, 720p, and 1080p HD. Quality depends on the source — we recommend 720p for the best balance of quality and loading speed."],

                ['q' => "Can I download episodes?",
                 'a' => "Downloads are not officially supported. We recommend streaming directly to support the platform and the source providers."],

                ['q' => "How often is new content added?",
                 'a' => "We update our library daily with new episodes and chapters. Seasonal anime are added within hours of release in most cases."],
            ],
        ],
        [
            'category' => 'Account',
            'icon'     => 'fa-user',
            'color'    => 'emerald',
            'items'    => [
                ['q' => "How do I create an account?",
                 'a' => "Click the Register button at the top of the page, enter your name, email, and a secure password. You'll receive a verification email to confirm your address."],

                ['q' => "I forgot my password. What do I do?",
                 'a' => "Click \"Forgot password?\" on the login page. Enter your email and we'll send you a reset link within a few minutes. Check your spam folder if you don't see it."],

                ['q' => "How do I change my email or password?",
                 'a' => "Go to your Profile Settings (top-right menu). You can update your email, password, and personal info there. For password changes you'll need to confirm with your current password."],

                ['q' => "How do I delete my account?",
                 'a' => "Go to Profile Settings → Danger Zone → Delete Account. You'll need to type DELETE and confirm with your password. This action is permanent and cannot be undone."],
            ],
        ],
        [
            'category' => 'Features',
            'icon'     => 'fa-star',
            'color'    => 'amber',
            'items'    => [
                ['q' => "What is the watchlist?",
                 'a' => "Your watchlist is a private collection of anime you want to watch later. Click the bookmark icon on any anime card or detail page to add it. You can manage your list from your profile."],

                ['q' => "Does {$brand} have a mobile app?",
                 'a' => "Not yet, but our website is fully responsive and works beautifully on phones and tablets. You can add it to your home screen for an app-like experience."],

                ['q' => "Can I comment on episodes?",
                 'a' => "Yes! Logged-in users can comment and react on any episode or anime page. Please be respectful — see our Terms of Service for community guidelines."],
            ],
        ],
        [
            'category' => 'Legal & Support',
            'icon'     => 'fa-shield-halved',
            'color'    => 'sky',
            'items'    => [
                ['q' => "How do I report broken content?",
                 'a' => "Use the report button on the player or anime page, or contact us through the Contact form with the URL and a description of the issue."],

                ['q' => "How do I submit a DMCA takedown?",
                 'a' => "Visit our DMCA Policy page for the full procedure. All copyright notices should be sent to our designated agent — see the DMCA page for the email address."],

                ['q' => "How can I support {$brand}?",
                 'a' => "Share us with friends, join our Discord community, and report bugs or broken content when you find them. Your engagement is what keeps the platform alive!"],
            ],
        ],
    ];
@endphp

@section('content')
<div
    class="max-w-5xl mx-auto py-10 px-4 space-y-8"
    x-data="{
        search: '',
        open: null,
        matches(text) {
            if (!this.search) return true;
            return text.toLowerCase().includes(this.search.toLowerCase());
        }
    }"
>

    {{-- ─────── HERO ─────── --}}
    <section class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/15 border border-indigo-500/30 mb-4">
            <i class="fas fa-circle-question text-indigo-400 text-2xl"></i>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">
            Frequently Asked <span class="text-indigo-400">Questions</span>
        </h1>

        <p class="text-base text-gray-400 max-w-2xl mx-auto">
            Quick answers to common questions about {{ $brand }}.
        </p>
    </section>

    {{-- ─────── SEARCH BAR ─────── --}}
    <div class="relative max-w-xl mx-auto">
        <input
            type="search"
            x-model="search"
            placeholder="Search questions…"
            class="w-full bg-gray-900 border border-gray-800 rounded-full pl-11 pr-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
        >
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>

        <button
            x-show="search"
            x-cloak
            @click="search = ''"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white text-sm transition"
            aria-label="Clear search"
        >
            ✕
        </button>
    </div>

    {{-- ─────── FAQ CATEGORIES ─────── --}}
    @php
        $colorMap = [
            'indigo'  => 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30',
            'pink'    => 'bg-pink-500/15 text-pink-400 border-pink-500/30',
            'emerald' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
            'amber'   => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
            'sky'     => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
        ];
    @endphp

    <div class="space-y-6">
        @foreach($faqs as $catIdx => $category)
            <section
                x-data
                x-show="$root.search === '' || {{ collect($category['items'])->map(fn($i) => "matches('".addslashes($i['q'].' '.$i['a'])."')")->implode(' || ') }}"
                x-cloak
            >

                {{-- CATEGORY HEADER --}}
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $colorMap[$category['color']] }}">
                        <i class="fas {{ $category['icon'] }}"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-white">{{ $category['category'] }}</h2>
                        <p class="text-xs text-gray-500">{{ count($category['items']) }} {{ Str::plural('question', count($category['items'])) }}</p>
                    </div>
                </div>

                {{-- QUESTIONS --}}
                <div class="card divide-y divide-gray-800">
                    @foreach($category['items'] as $itemIdx => $item)
                        @php
                            $key = "c{$catIdx}q{$itemIdx}";
                        @endphp

                        <div x-show="matches('{{ addslashes($item['q'].' '.$item['a']) }}')" x-cloak>
                            <button
                                @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                                class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 hover:bg-white/[0.02] transition"
                                :aria-expanded="open === '{{ $key }}'"
                            >
                                <span class="text-sm font-medium text-white">
                                    {{ $item['q'] }}
                                </span>

                                <svg class="w-4 h-4 text-gray-500 shrink-0 transition-transform"
                                     :class="open === '{{ $key }}' && 'rotate-180 text-indigo-400'"
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div
                                x-show="open === '{{ $key }}'"
                                x-cloak
                                x-collapse
                                class="px-5 pb-4 text-sm text-gray-400 leading-relaxed"
                            >
                                {{ $item['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

            </section>
        @endforeach
    </div>

    {{-- ─────── NO RESULTS ─────── --}}
    <div
        x-show="search && !{{ collect($faqs)->flatMap(fn($c) => $c['items'])->map(fn($i) => "matches('".addslashes($i['q'].' '.$i['a'])."')")->implode(' || ') }}"
        x-cloak
        class="text-center py-10"
    >
        <div class="inline-flex w-16 h-16 rounded-full bg-gray-800 items-center justify-center mb-3">
            <i class="fas fa-magnifying-glass text-gray-600 text-xl"></i>
        </div>
        <p class="text-white font-medium">No results found</p>
        <p class="text-sm text-gray-500 mt-1">Try a different keyword or browse the categories above.</p>
    </div>

    {{-- ─────── STILL NEED HELP CTA ─────── --}}
    <section class="relative overflow-hidden rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-pink-600/20 p-6 sm:p-8 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/10 mb-3">
            <i class="fas fa-headset text-white"></i>
        </div>

        <h2 class="text-xl font-bold text-white mb-2">
            Still need help?
        </h2>
        <p class="text-sm text-gray-300 max-w-md mx-auto mb-5">
            Can't find what you're looking for? Our team is here to help.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            {{ route('static.page', 'contact') }} class="btn-primary">
                <i class="fas fa-envelope"></i>
                Contact Support
            </a>
            "
               target="_blank" rel="noopener noreferrer"
               class="btn-outline">
                <i class="fab fa-discord"></i>
                Join Discord
            </a>
        </div>
    </section>

</div>
@endsection