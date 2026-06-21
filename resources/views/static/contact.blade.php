@extends('layouts.main')

@section('title', 'Contact Us')
@section('description', 'Get in touch with the ' . config('app.name', 'AniKoto') . ' team. We\'d love to hear from you.')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4 space-y-10">

    {{-- ─────── HERO ─────── --}}
    <section class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/15 border border-indigo-500/30 mb-4">
            <i class="fas fa-envelope text-indigo-400 text-2xl"></i>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">
            Get in <span class="text-indigo-400">Touch</span>
        </h1>

        <p class="text-base text-gray-400 max-w-2xl mx-auto">
            Got questions, feedback, or just want to say hi? We'd love to hear from you.
        </p>
    </section>

    {{-- ─────── GRID ─────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ─────── LEFT: INFO ─────── --}}
        <aside class="lg:col-span-2 space-y-4">

            {{-- Email --}}
            <div class="card p-5 hover:border-gray-700 transition">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">Email Support</p>
                        <p class="text-xs text-gray-500 mb-1">For general inquiries</p>
                        @{{ Str::slug(config('app.name', 'AniKoto')) }}.com"
                           class="text-sm text-indigo-400 hover:text-indigo-300 break-all transition">
                            contact@{{ Str::slug(config('app.name', 'AniKoto')) }}.com
                        </a>
                    </div>
                </div>
            </div>

            {{-- DMCA --}}
            <div class="card p-5 hover:border-gray-700 transition">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">DMCA / Copyright</p>
                        <p class="text-xs text-gray-500 mb-1">For takedown requests</p>
                        @{{ Str::slug(config('app.name', 'AniKoto')) }}.com"
                           class="text-sm text-indigo-400 hover:text-indigo-300 break-all transition">
                            dmca@{{ Str::slug(config('app.name', 'AniKoto')) }}.com
                        </a>
                    </div>
                </div>
            </div>

            {{-- Discord --}}
            <div class="card p-5 hover:border-gray-700 transition">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-violet-500/15 text-violet-400 flex items-center justify-center shrink-0">
                        <i class="fab fa-discord"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">Discord Community</p>
                        <p class="text-xs text-gray-500 mb-1">Join the conversation</p>
                        "
                           target="_blank" rel="noopener noreferrer"
                           class="text-sm text-indigo-400 hover:text-indigo-300 transition">
                            Join our server →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Response time --}}
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4">
                <p class="text-xs text-emerald-300 flex items-start gap-2">
                    <i class="fas fa-clock mt-0.5"></i>
                    <span>We aim to respond to all inquiries within <strong>24–48 hours</strong>.</span>
                </p>
            </div>

        </aside>

        {{-- ─────── RIGHT: CONTACT FORM ─────── --}}
        <div class="lg:col-span-3">

            <div class="card p-6 sm:p-8">

                <h2 class="text-lg font-semibold text-white mb-1">
                    Send us a message
                </h2>
                <p class="text-sm text-gray-400 mb-6">
                    Fill out the form below and we'll get back to you as soon as possible.
                </p>

                {{-- Success message --}}
                @if (session('contact_sent'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 8000)"
                        class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 flex items-center gap-2"
                    >
                        <i class="fas fa-circle-check"></i>
                        Thanks! Your message has been sent. We'll be in touch soon.
                    </div>
                @endif

                {{ route('contact.send') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Name + Email row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label for="name" class="form-label">Your Name</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', auth()->user()->name ?? '') }}"
                                required
                                placeholder="Your full name"
                                class="form-input"
                            >
                            <x-input-error :messages="$errors->get('name')" class="form-error" />
                        </div>

                        <div>
                            <label for="email" class="form-label">Your Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', auth()->user()->email ?? '') }}"
                                required
                                placeholder="you@example.com"
                                class="form-input"
                            >
                            <x-input-error :messages="$errors->get('email')" class="form-error" />
                        </div>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label for="subject" class="form-label">Subject</label>
                        <select id="subject" name="subject" required class="form-select">
                            <option value="">Choose a topic...</option>
                            <option value="general" @selected(old('subject') === 'general')>General Inquiry</option>
                            <option value="bug" @selected(old('subject') === 'bug')>Report a Bug</option>
                            <option value="feature" @selected(old('subject') === 'feature')>Feature Request</option>
                            <option value="content" @selected(old('subject') === 'content')>Missing / Broken Content</option>
                            <option value="dmca" @selected(old('subject') === 'dmca')>DMCA / Copyright</option>
                            <option value="partnership" @selected(old('subject') === 'partnership')>Partnership / Business</option>
                            <option value="other" @selected(old('subject') === 'other')>Other</option>
                        </select>
                        <x-input-error :messages="$errors->get('subject')" class="form-error" />
                    </div>

                    {{-- Message --}}
                    <div x-data="{ count: {{ strlen(old('message', '')) }}, max: 2000 }">
                        <div class="flex items-center justify-between">
                            <label for="message" class="form-label">Message</label>
                            <span class="text-xs text-gray-500">
                                <span x-text="count"></span> / <span x-text="max"></span>
                            </span>
                        </div>

                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            required
                            maxlength="2000"
                            @input="count = $event.target.value.length"
                            placeholder="Tell us what's on your mind..."
                            class="form-textarea"
                        >{{ old('message') }}</textarea>

                        <x-input-error :messages="$errors->get('message')" class="form-error" />
                    </div>

                    {{-- Honeypot (anti-spam) --}}
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                    {{-- Submit --}}
                    <div class="flex items-center justify-between pt-2">
                        <p class="text-xs text-gray-500 hidden sm:block">
                            <i class="fas fa-shield-alt"></i>
                            Your information is secure and never shared.
                        </p>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ─────── HELPFUL LINKS ─────── --}}
    <section class="text-center pt-4">
        <p class="text-sm text-gray-500">
            Looking for quick answers?
            {{ route('static.page', 'faq') }} class="text-indigo-400 hover:text-indigo-300 hover:underline transition">
                Check our FAQ →
            </a>
        </p>
    </section>

</div>
@endsection