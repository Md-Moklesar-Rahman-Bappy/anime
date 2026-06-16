@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-white">
            Dashboard
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            Welcome back, {{ auth()->user()->name }}
        </p>
    </div>

    <!-- Content -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6">

        <p class="text-gray-300">
            ✅ You are logged in successfully.
        </p>

        <div class="mt-4 text-sm text-gray-400">
            Explore anime, manage your profile, or continue watching your favorite series.
        </div>

    </div>

</div>
@endsection