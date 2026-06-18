@extends('layouts.main')

@section('title', 'DMCA')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-semibold text-white">
            DMCA Policy
        </h1>
        <p class="text-gray-400 text-sm mt-2">
            Copyright compliance and content removal
        </p>
    </div>

    <!-- Content Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-5 text-gray-400 leading-relaxed">

        <p>
            AniWaves respects the intellectual property rights of others and complies with applicable copyright laws.
        </p>

        <p>
            If you believe that any content available on our platform infringes your copyright, you may submit a request for removal.
        </p>

        <p>
            To process your claim efficiently, please provide:
        </p>

        <ul class="list-disc list-inside space-y-1">
            <li>Your full name and contact information</li>
            <li>A description of the copyrighted work</li>
            <li>The exact URL(s) of the infringing content</li>
            <li>A statement confirming your ownership or authorization</li>
        </ul>

        <p>
            Send all DMCA notices to:
        </p>

        <div class="flex items-center gap-2 pt-1">
            <span class="text-indigo-400">✉</span>
            <a href="mailto:contact@aniwaves.ru"
               class="text-indigo-400 hover:text-indigo-300 font-medium transition">
                contact@aniwaves.ru
            </a>
        </div>

        <p class="text-sm text-gray-500 pt-2">
            We take all reports seriously and will take appropriate action when necessary.
        </p>

    </div>

</div>
@endsection