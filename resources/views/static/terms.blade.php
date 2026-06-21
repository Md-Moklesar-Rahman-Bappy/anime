@extends('layouts.main')

@section('title', 'Terms of Service')
@section('description', 'Read the Terms of Service for ' . config('app.name', 'AniKoto') . ' — your rights and responsibilities when using our platform.')

@php
    $brand        = config('app.name', 'AniKoto');
    $brandSlug    = \Illuminate\Support\Str::slug($brand);
    $contactEmail = config('mail.support_address', 'support@' . $brandSlug . '.com');
    $lastUpdated  = 'June 1, 2026';
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 space-y-8">

    {{-- ─────── HEADER ─────── --}}
    <section class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/15 border border-indigo-500/30 mb-4">
            <i class="fas fa-scale-balanced text-indigo-400 text-2xl"></i>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">
            Terms of <span class="text-indigo-400">Service</span>
        </h1>

        <p class="text-base text-gray-400 max-w-2xl mx-auto">
            Please read these terms carefully before using {{ $brand }}.
        </p>

        <p class="mt-3 text-xs text-gray-500">
            <i class="fas fa-calendar"></i> Last updated: <strong>{{ $lastUpdated }}</strong>
        </p>
    </section>

    {{-- ─────── AGREEMENT NOTICE ─────── --}}
    <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/5 p-4 sm:p-5">
        <p class="text-sm text-indigo-200 flex items-start gap-3">
            <i class="fas fa-circle-info text-base mt-0.5 shrink-0"></i>
            <span>
                <strong class="text-white">Important:</strong>
                By accessing or using {{ $brand }}, you agree to be bound by these Terms of Service.
                If you do not agree to these terms, please do not use our platform.
            </span>
        </p>
    </div>

    {{-- ─────── TABLE OF CONTENTS ─────── --}}
    <nav class="card p-5">
        <p class="text-xs uppercase tracking-wider text-gray-500 mb-3 font-semibold">
            On this page
        </p>
        <ol class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
            <li>#-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">01</span> Acceptance of Terms</a></li>
            <li>#text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">02</span> Use of the Service</a></li>
            <li>#-text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">03</span> User Accounts</a></li>
            <li>#user-conduct" class="text-gray-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">04</span> User Conduct</a></li>
            <li>#text-gray-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">05</span> Content & Copyright</a></li>
            <li>#-content" class="text-gray-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">06</span> User Content</a></li>
            <li>#text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">07</span> Account Termination</a></li>
            <li>#-text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">08</span> Disclaimers</a></li>
            <li>#-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">09</span> Limitation of Liability</a></li>
            <li>#-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">10</span> Changes to Terms</a></li>
            <li>#-text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">11</span> Governing Law</a></li>
            <li>#-400 hover:text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">12</span> Contact</a></li>
        </ol>
    </nav>

    {{-- ─────── SECTIONS ─────── --}}
    <div class="card divide-y divide-gray-800">

        {{-- 01 ACCEPTANCE --}}
        <section id="acceptance" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">01.</span> Acceptance of Terms
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    By accessing or using {{ $brand }} (the "Service"), you agree to be legally bound by these Terms of Service ("Terms"), our Privacy Policy, and our DMCA Policy.
                </p>
                <p>
                    These Terms constitute a legally binding agreement between you and {{ $brand }}. If you are using the Service on behalf of an organization, you represent that you have authority to bind that organization to these Terms.
                </p>
            </div>
        </section>

        {{-- 02 USE --}}
        <section id="use" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">02.</span> Use of the Service
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    {{ $brand }} is provided for informational and entertainment purposes only. We do not host, upload, or store any media files on our servers — we only index and link to media hosted on third-party platforms.
                </p>
                <p>
                    You must be at least <strong class="text-gray-200">13 years old</strong> to use our Service. If you are under 18, you may only use the Service with the involvement of a parent or guardian.
                </p>
            </div>
        </section>

        {{-- 03 ACCOUNTS --}}
        <section id="accounts" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">03.</span> User Accounts
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    Some features of our Service require an account. You are responsible for:
                </p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                    <li>Providing accurate and complete information during registration</li>
                    <li>Maintaining the confidentiality of your password</li>
                    <li>All activities that occur under your account</li>
                    <li>Notifying us immediately of any unauthorized access</li>
                </ul>
                <p>
                    We reserve the right to refuse registration or terminate accounts at our sole discretion.
                </p>
            </div>
        </section>

        {{-- 04 CONDUCT --}}
        <section id="user-conduct" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">04.</span> User Conduct
            </h2>
            <p class="text-sm text-gray-400 mb-3">
                You agree <strong class="text-gray-200">not</strong> to use the Service to:
            </p>
            <ul class="space-y-3">
                @foreach([
                    'Violate any applicable laws or regulations',
                    'Infringe the intellectual property rights of others',
                    'Harass, abuse, or harm other users',
                    'Post spam, malware, or any malicious content',
                    'Attempt to gain unauthorized access to any part of the Service',
                    'Use bots, scrapers, or automated tools to collect data from the Service',
                    'Impersonate any person or misrepresent your affiliation',
                    'Interfere with the proper functioning of the Service',
                ] as $i => $rule)
                    <li class="flex items-start gap-3 text-sm text-gray-400">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-red-500/15 text-red-400 text-xs font-semibold flex items-center justify-center mt-0.5">
                            {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>{{ $rule }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- 05 CONTENT --}}
        <section id="content" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">05.</span> Content & Copyright
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    {{ $brand }} respects intellectual property rights. We comply with the Digital Millennium Copyright Act (DMCA) and respond to valid takedown notices.
                </p>
                <p>
                    If you believe content on our Service infringes your copyright, please review our
                    {{ route('static.page', 'dmca') }} class="text-indigo-400 hover:text-indigo-300 transition">DMCA Policy</a>
                    for the takedown procedure.
                </p>
            </div>
        </section>

        {{-- 06 USER CONTENT --}}
        <section id="user-content" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">06.</span> User-Generated Content
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    By posting comments, reviews, or other content on {{ $brand }}, you grant us a non-exclusive, worldwide, royalty-free license to use, display, and distribute that content on our Service.
                </p>
                <p>
                    You are solely responsible for the content you post. We reserve the right to remove any content that violates these Terms or is otherwise objectionable, at our sole discretion.
                </p>
            </div>
        </section>

        {{-- 07 TERMINATION --}}
        <section id="termination" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">07.</span> Account Termination
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    We reserve the right to suspend or terminate your account at any time, without notice, for conduct we believe violates these Terms or is harmful to other users, us, or third parties.
                </p>
                <p>
                    You may delete your account at any time through your Profile Settings. Account deletion is permanent and cannot be undone.
                </p>
            </div>
        </section>

        {{-- 08 DISCLAIMERS --}}
        <section id="disclaimers" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">08.</span> Disclaimers
            </h2>
            <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 p-4 mb-4">
                <p class="text-xs text-amber-300 flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                    <span>The Service is provided <strong>"AS IS" and "AS AVAILABLE"</strong> without warranties of any kind, either express or implied.</span>
                </p>
            </div>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    {{ $brand }} does not warrant that the Service will be uninterrupted, secure, error-free, or free of viruses or other harmful components.
                </p>
                <p>
                    We are not responsible for the content, accuracy, or availability of third-party links or media. Use of third-party services is at your own risk.
                </p>
            </div>
        </section>

        {{-- 09 LIABILITY --}}
        <section id="liability" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">09.</span> Limitation of Liability
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    To the maximum extent permitted by law, {{ $brand }} and its operators, employees, and affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of your use of, or inability to use, the Service.
                </p>
                <p>
                    Our total liability for any claim arising out of or relating to these Terms or the Service shall not exceed <strong class="text-gray-200">one hundred US dollars ($100)</strong>.
                </p>
            </div>
        </section>

        {{-- 10 CHANGES --}}
        <section id="changes" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">10.</span> Changes to These Terms
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    We may modify these Terms at any time. When we do, we will update the "Last updated" date at the top of this page.
                </p>
                <p>
                    Continued use of the Service after changes constitutes acceptance of the updated Terms. If you do not agree to the changes, please discontinue using the Service.
                </p>
            </div>
        </section>

        {{-- 11 GOVERNING LAW --}}
        <section id="governing-law" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">11.</span> Governing Law
            </h2>
            <p class="text-sm text-gray-400 leading-relaxed">
                These Terms shall be governed by and construed in accordance with applicable international law principles. Any disputes arising shall be resolved through good-faith negotiation, and if unresolved, through binding arbitration.
            </p>
        </section>

        {{-- 12 CONTACT --}}
        <section id="contact" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-indigo-400 mr-2">12.</span> Contact Us
            </h2>
            <p class="text-sm text-gray-400 mb-4">
                If you have questions about these Terms, please contact us:
            </p>

            <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/5 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Support</p>
                        ={{ $brand }} - Terms Inquiry"
                           class="text-base font-semibold text-indigo-300 hover:text-indigo-200 transition break-all">
                            {{ $contactEmail }}
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-4">
                Or use our
                {{ route('static.page', 'contact') }} class="text-indigo-400 hover:text-indigo-300 transition">
                    contact form →
                </a>
            </p>
        </section>

    </div>

    {{-- ─────── FINAL NOTE ─────── --}}
    <section class="text-center text-sm text-gray-500">
        <p>
            By using {{ $brand }}, you acknowledge that you have read, understood, and agree to these Terms of Service.
        </p>
    </section>

</div>
@endsection