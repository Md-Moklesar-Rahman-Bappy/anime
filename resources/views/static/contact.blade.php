@extends('layouts.main')

@section('title', 'Contact')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-semibold text-white">
            Contact Us
        </h1>
        <p class="text-gray-400 text-sm mt-2">
            We’d love to hear from you
        </p>
    </div>

    <!-- Contact Card -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-4 text-gray-400">

        <p>
            If you have any questions, feedback, or inquiries, feel free to reach out to us.
        </p>

        <div class="flex items-center gap-3">

            <!-- Icon -->
            <span class="text-indigo-400 text-lg">
                ✉
            </span>

            <!-- Email -->
            <a href="mailto:contact@aniwaves.ru"
               class="text-indigo-400 hover:text-indigo-300 transition font-medium">
                contact@aniwaves.ru
            </a>

        </div>

        <p class="text-sm text-gray-500 pt-2">
            We aim to respond to all inquiries as quickly as possible.
        </p>

    </div>

</div>
@endsection