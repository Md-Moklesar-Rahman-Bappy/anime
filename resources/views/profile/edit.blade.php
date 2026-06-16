@extends('layouts.main')

@section('title', 'Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Profile') }}</h1>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
