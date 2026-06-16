@extends('layouts.main')

@section('title', 'Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-white">
            Profile Settings
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            Manage your account information and security
        </p>
    </div>

    <!-- Sections -->
    <div class="space-y-6">

        <!-- Profile Info -->
        <div class="card">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Password -->
        <div class="card">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete -->
        <div class="card border-red-500/10">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>

</div>

<style>
.card {
    @apply bg-[#111827] border border-gray-800 rounded-2xl p-6 shadow;
}
</style>

@endsection