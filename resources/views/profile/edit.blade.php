@extends('layouts.main')

@section('title', 'Profile Settings')
@section('description', 'Manage your AniKoto account, profile, and security settings.')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ tab: window.location.hash.replace('#','') || 'profile' }"
     x-init="$watch('tab', v => history.replaceState(null, '', '#' + v))">

    {{-- ─────── PAGE HEADER ─────── --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">
            Account Settings
        </h1>
        <p class="mt-1 text-sm text-gray-400">
            Manage your profile, password, and account preferences.
        </p>
    </div>

    {{-- ─────── LAYOUT GRID ─────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- ───── SIDEBAR ───── --}}
        <aside class="lg:col-span-1">
            <div class="card p-4 lg:sticky lg:top-20">

                {{-- USER INFO --}}
                <div class="flex items-center gap-3 pb-4 border-b border-gray-800 mb-3">
                    ={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff&size=64"
                         class="w-10 h-10 rounded-full"
                         alt="{{ auth()->user()->name }}"
                    >
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>

                {{-- TABS --}}
                <nav class="space-y-1 text-sm">

                    <button @click="tab = 'profile'"
                            class="settings-tab"
                            :class="tab === 'profile' && 'settings-tab-active'">
                        <i class="fas fa-user w-4 text-center"></i>
                        Profile
                    </button>

                    <button @click="tab = 'password'"
                            class="settings-tab"
                            :class="tab === 'password' && 'settings-tab-active'">
                        <i class="fas fa-key w-4 text-center"></i>
                        Password
                    </button>

                    <div class="border-t border-gray-800 my-2"></div>

                    <button @click="tab = 'danger'"
                            class="settings-tab text-red-400 hover:!bg-red-500/10"
                            :class="tab === 'danger' && '!bg-red-500/10 !text-red-300'">
                        <i class="fas fa-triangle-exclamation w-4 text-center"></i>
                        Danger Zone
                    </button>

                </nav>

                {{-- BACK TO SITE --}}
                <div class="mt-4 pt-3 border-t border-gray-800">
                    {{ route('home') }} flex items-center gap-2 px-3 py-2 text-xs text-gray-500 hover:text-white transition">
                        <i class="fas fa-arrow-left"></i>
                        Back to site
                    </a>
                </div>
            </div>
        </aside>

        {{-- ───── CONTENT ───── --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- PROFILE TAB --}}
            <div x-show="tab === 'profile'" x-cloak x-transition>
                <div class="card p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- PASSWORD TAB --}}
            <div x-show="tab === 'password'" x-cloak x-transition>
                <div class="card p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- DANGER ZONE TAB --}}
            <div x-show="tab === 'danger'" x-cloak x-transition>
                <div class="rounded-2xl border border-red-500/20 bg-red-500/[0.02] p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .settings-tab {
        @apply w-full text-left flex items-center gap-3 px-3 py-2 rounded-md
               text-gray-400 hover:text-white hover:bg-white/5 transition;
    }
    .settings-tab-active {
        @apply bg-indigo-600/20 text-white;
    }
</style>
@endpush