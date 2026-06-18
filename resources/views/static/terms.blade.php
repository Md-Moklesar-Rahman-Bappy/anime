@extends('layouts.main')

@section('title', 'Terms of Service')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-semibold text-white">
            Terms of Service
        </h1>
        <p class="text-gray-400 text-sm mt-2">
            Please read these terms carefully before using AniWaves
        </p>
    </div>

    <!-- Content Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-5 text-gray-400 leading-relaxed">

        <p>
            By accessing or using <span class="text-white font-medium">AniWaves</span>, you agree to comply with these Terms of Service.
        </p>

        <p>
            Our platform is provided for informational and entertainment purposes only.
            We do not host any content directly on our servers.
        </p>

        <p>
            You agree not to use the service for any unlawful activities or in violation of any applicable laws and regulations.
        </p>

        <p>
            AniWaves reserves the right to modify or update these terms at any time.
            Continued use of the service after changes indicates your acceptance of the updated terms.
        </p>

        <p class="text-sm text-gray-500 pt-2">
            If you do not agree with any part of these terms, please discontinue use of the site.
        </p>

    </div>

</div>
@endsection