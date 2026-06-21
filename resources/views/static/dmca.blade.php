@extends('layouts.main')

@section('title', 'DMCA Policy')
@section('description', 'DMCA copyright policy and content removal procedure for ' . config('app.name', 'AniKoto') . '.')

@php
    $brand        = config('app.name', 'AniKoto');
    $brandSlug    = \Illuminate\Support\Str::slug($brand);
    $contactEmail = config('mail.dmca_address', 'dmca@' . $brandSlug . '.com');
    $lastUpdated  = 'June 1, 2026';
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 space-y-8">

    {{-- ─────── HEADER ─────── --}}
    <section class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500/15 border border-amber-500/30 mb-4">
            <i class="fas fa-shield-halved text-amber-400 text-2xl"></i>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">
            DMCA <span class="text-amber-400">Policy</span>
        </h1>

        <p class="text-base text-gray-400 max-w-2xl mx-auto">
            Copyright compliance and content removal procedures
        </p>

        <p class="mt-3 text-xs text-gray-500">
            <i class="fas fa-calendar"></i> Last updated: <strong>{{ $lastUpdated }}</strong>
        </p>
    </section>

    {{-- ─────── IMPORTANT NOTICE ─────── --}}
    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 sm:p-5">
        <p class="text-sm text-amber-300 flex items-start gap-3">
            <i class="fas fa-circle-info text-base mt-0.5 shrink-0"></i>
            <span>
                <strong class="text-amber-200">Important:</strong>
                {{ $brand }} does <strong>not host, upload, or store</strong> any media files on our servers.
                We only provide links to media hosted on third-party services. All copyright concerns should
                be directed to the original content hosts whenever possible.
            </span>
        </p>
    </div>

    {{-- ─────── TABLE OF CONTENTS ─────── --}}
    <nav class="card p-5">
        <p class="text-xs uppercase tracking-wider text-gray-500 mb-3 font-semibold">
            On this page
        </p>
        <ol class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
            <li>#-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">01</span> Our Policy</a></li>
            <li>#-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">02</span> Filing a Notice</a></li>
            <li>#text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">03</span> Required Information</a></li>
            <li>#400 transition flex items-center gap-2"><span class="text-gray-600">04</span> Submitting Your Notice</a></li>
            <li>#text-indigo-400 transition flex items-center gap-2"><span class="text-gray-600">05</span> Counter-Notification</a></li>
            <li>#400 transition flex items-center gap-2"><span class="text-gray-600">06</span> Repeat Infringers</a></li>
        </ol>
    </nav>

    {{-- ─────── SECTIONS ─────── --}}
    <div class="card divide-y divide-gray-800">

        {{-- 01 POLICY --}}
        <section id="policy" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">01.</span> Our Policy
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    {{ $brand }} respects the intellectual property rights of others and expects our users to do
                    the same. In accordance with the Digital Millennium Copyright Act (DMCA) of 1998, we will
                    respond expeditiously to claims of copyright infringement.
                </p>
                <p>
                    If you are a copyright owner or authorized agent and believe that material on this site
                    infringes your copyright, you may submit a DMCA notice following the procedure below.
                </p>
            </div>
        </section>

        {{-- 02 FILING --}}
        <section id="filing" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">02.</span> Filing a DMCA Notice
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    To file a DMCA takedown notice, please send a written communication that includes
                    all of the information listed in the next section.
                </p>
                <p>
                    Be aware that under Section 512(f) of the DMCA, knowingly making false claims may
                    result in civil liability for damages, including costs and attorney's fees.
                </p>
            </div>
        </section>

        {{-- 03 REQUIRED INFO --}}
        <section id="required" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">03.</span> Required Information
            </h2>
            <p class="text-sm text-gray-400 mb-4">
                Your notice <strong class="text-gray-200">must include</strong>:
            </p>

            <ul class="space-y-3">
                @foreach([
                    'Your full legal name and contact information (email, phone, address)',
                    'A clear description of the copyrighted work you claim has been infringed',
                    'The exact URL(s) of the allegedly infringing content on our site',
                    'A statement of good faith belief that the use is not authorized by the copyright owner',
                    'A statement, under penalty of perjury, that the information is accurate and you are authorized to act on behalf of the owner',
                    'Your physical or electronic signature',
                ] as $i => $item)
                    <li class="flex items-start gap-3 text-sm text-gray-400">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-amber-500/15 text-amber-400 text-xs font-semibold flex items-center justify-center mt-0.5">
                            {{ $i + 1 }}
                        </span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- 04 SUBMIT --}}
        <section id="submit" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">04.</span> Submitting Your Notice
            </h2>
            <p class="text-sm text-gray-400 mb-4">
                Send your complete DMCA notice to our designated copyright agent:
            </p>

            <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/5 p-5">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">DMCA Agent</p>
                        ={{ $brand }} - DMCA Notice"
                           class="text-base font-semibold text-indigo-300 hover:text-indigo-200 transition break-all">
                            {{ $contactEmail }}
                        </a>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-3 flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    Response time: typically within <strong>48–72 hours</strong>
                </p>
            </div>
        </section>

        {{-- 05 COUNTER --}}
        <section id="counter" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">05.</span> Counter-Notification
            </h2>
            <div class="space-y-3 text-sm text-gray-400 leading-relaxed">
                <p>
                    If you believe your content was removed by mistake or misidentification,
                    you may file a counter-notification with us. It must include:
                </p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                    <li>Your physical or electronic signature</li>
                    <li>Identification of the removed material and its previous URL</li>
                    <li>A statement under penalty of perjury that the removal was a mistake or misidentification</li>
                    <li>Your name, address, phone number, and consent to local jurisdiction</li>
                </ul>
                <p>
                    Send counter-notifications to the same email address listed above.
                </p>
            </div>
        </section>

        {{-- 06 REPEAT --}}
        <section id="repeat" class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white mb-3">
                <span class="text-amber-400 mr-2">06.</span> Repeat Infringers
            </h2>
            <p class="text-sm text-gray-400 leading-relaxed">
                In accordance with the DMCA and applicable laws, {{ $brand }} maintains a policy of
                terminating, in appropriate circumstances, accounts of users deemed to be repeat
                infringers of copyright.
            </p>
        </section>

    </div>

    {{-- ─────── HELP CTA ─────── --}}
    <section class="text-center pt-2">
        <p class="text-sm text-gray-500">
            Have questions about our policy?
            {{ route('static.page', 'contact') }} class="text-indigo-400 hover:text-indigo-300 hover:underline transition">
                Contact our team →
            </a>
        </p>
    </section>

</div>
@endsection