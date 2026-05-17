@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold mb-8">Frequently Asked Questions</h1>
    <div x-data="{ open: null }" class="space-y-4">
        <div class="bg-gray-900 rounded-lg">
            <button @click="open = open === 1 ? null : 1" class="w-full text-left px-6 py-4 flex justify-between items-center">
                <span class="font-semibold">What is AniWaves?</span>
                <svg class="w-5 h-5 transition" :class="open === 1 ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </button>
            <div x-show="open === 1" class="px-6 pb-4 text-gray-400 text-sm">AniWaves is a free anime streaming platform where you can watch your favorite anime episodes online.</div>
        </div>
        <div class="bg-gray-900 rounded-lg">
            <button @click="open = open === 2 ? null : 2" class="w-full text-left px-6 py-4 flex justify-between items-center">
                <span class="font-semibold">Is AniWaves free?</span>
                <svg class="w-5 h-5 transition" :class="open === 2 ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </button>
            <div x-show="open === 2" class="px-6 pb-4 text-gray-400 text-sm">Yes, AniWaves is completely free to use. No subscription required.</div>
        </div>
        <div class="bg-gray-900 rounded-lg">
            <button @click="open = open === 3 ? null : 3" class="w-full text-left px-6 py-4 flex justify-between items-center">
                <span class="font-semibold">Do I need to create an account?</span>
                <svg class="w-5 h-5 transition" :class="open === 3 ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
            </button>
            <div x-show="open === 3" class="px-6 pb-4 text-gray-400 text-sm">You can browse without an account, but you need to register to comment, favorite anime, and track your watch history.</div>
        </div>
    </div>
</div>
@endsection
