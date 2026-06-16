@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-3xl font-semibold text-white">
            Frequently Asked Questions
        </h1>
        <p class="text-gray-400 text-sm mt-2">
            Everything you need to know about AniWaves
        </p>
    </div>

    <!-- FAQ -->
    <div x-data="{ open: null }" class="space-y-4">

        <!-- Item -->
        @foreach([
            [1, 'What is AniWaves?', 'AniWaves is a free anime streaming platform where you can watch your favorite anime episodes online.'],
            [2, 'Is AniWaves free?', 'Yes, AniWaves is completely free to use. No subscription required.'],
            [3, 'Do I need an account?', 'You can browse without an account, but registration is required for commenting, favorites, and tracking your watch history.']
        ] as [$id, $q, $a])

        <div class="faq-item">

            <button
                @click="open = open === {{ $id }} ? null : {{ $id }}"
                class="faq-question">

                <span class="text-white font-medium">
                    {{ $q }}
                </span>

                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                     :class="open === {{ $id }} ? 'rotate-180 text-indigo-400' : ''"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                </svg>

            </button>

            <div
                x-show="open === {{ $id }}"
                x-transition
                class="faq-answer">

                {{ $a }}

            </div>

        </div>

        @endforeach

    </div>

</div>

<style>
.faq-item {
    @apply bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden;
}

.faq-question {
    @apply w-full px-6 py-4 flex justify-between items-center text-left hover:bg-white/5 transition;
}

.faq-answer {
    @apply px-6 pb-4 text-sm text-gray-400 leading-relaxed;
}
</style>

@endsection
