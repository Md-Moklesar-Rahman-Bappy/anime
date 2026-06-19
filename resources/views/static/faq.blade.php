@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<div class="container" style="max-width:56rem;padding:3rem 1rem">

    <div class="text-center mb-4">
        <h1 class="fw-semibold" style="color:#fff;font-size:1.75rem">
            Frequently Asked Questions
        </h1>
        <p class="mt-2" style="color:#9ca3af;font-size:0.875rem">
            Everything you need to know about AniWaves
        </p>
    </div>

    <div x-data="{ open: null }" class="d-flex flex-column gap-3">

        @foreach([
            [1, 'What is AniWaves?', 'AniWaves is a free anime streaming platform where you can watch your favorite anime episodes online.'],
            [2, 'Is AniWaves free?', 'Yes, AniWaves is completely free to use. No subscription required.'],
            [3, 'Do I need an account?', 'You can browse without an account, but registration is required for commenting, favorites, and tracking your watch history.']
        ] as [$id, $q, $a])

        <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;overflow:hidden">

            <button
                @click="open = open === {{ $id }} ? null : {{ $id }}"
                class="w-100 d-flex justify-content-between align-items-center text-start px-4 py-3"
                style="background:none;border:none;color:#d1d5db;cursor:pointer">

                <span class="fw-medium" style="color:#fff">
                    {{ $q }}
                </span>

                <svg style="width:1.25rem;height:1.25rem;color:#9ca3af;transition:transform 0.3s"
                     :style="open === {{ $id }} ? 'transform:rotate(180deg);color:#818cf8' : 'transform:rotate(0deg)'"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                </svg>

            </button>

            <div
                x-show="open === {{ $id }}"
                x-transition
                style="padding:0 1.5rem 1rem 1.5rem;color:#9ca3af;font-size:0.875rem;line-height:1.625">

                {{ $a }}

            </div>

        </div>

        @endforeach

    </div>

</div>
@endsection
