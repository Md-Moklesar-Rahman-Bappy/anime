@extends('layouts.main')

@section('title', 'About')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Title -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-semibold text-white">
            About AniWaves
        </h1>
        <p class="text-gray-400 text-sm mt-2">
            Your ultimate anime streaming destination
        </p>
    </div>

    <!-- Content Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-5 text-gray-400 leading-relaxed">

        <p>
            <span class="text-white font-medium">AniWaves</span> is your go-to platform for streaming anime online. 
            We bring together a wide selection of anime series and movies — from timeless classics to the latest releases.
        </p>

        <p>
            Our goal is to create a seamless viewing experience for anime fans worldwide. 
            With organized categories like genres, types, and trending shows, discovering new anime has never been easier.
        </p>

        <p>
            We continuously update our library with new episodes and content to keep you up to date with your favorite shows.
        </p>

        <p class="text-indigo-400 font-medium">
            Sit back, relax, and enjoy the world of anime with AniWaves 🎬
        </p>

    </div>

</div>
@endsection