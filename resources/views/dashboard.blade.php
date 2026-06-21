@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-white">
            Dashboard
        </h1>
        <p class="text-gray-400 text-sm mt-1">
            Welcome back, {{ auth()->user()->name }}
        </p>
    </div>

    {{-- CARD --}}
    <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">

        <p class="text-green-400 font-medium">
            ✅ You are logged in successfully.
        </p>

        <p class="text-gray-400 text-sm mt-3">
            Explore anime, manage your profile, or continue watching your favorite series.
        </p>

    </div>

</div>

@endsection