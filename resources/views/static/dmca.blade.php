@extends('layouts.main')

@section('title', 'DMCA')

@section('content')
<div class="container" style="max-width:56rem;padding:3rem 1rem">

    <div class="text-center mb-4">
        <h1 class="fw-semibold" style="color:#fff;font-size:1.75rem">
            DMCA Policy
        </h1>
        <p class="mt-2" style="color:#9ca3af;font-size:0.875rem">
            Copyright compliance and content removal
        </p>
    </div>

    <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.5rem;color:#9ca3af;line-height:1.625" class="d-flex flex-column gap-3">

        <p>
            AniWaves respects the intellectual property rights of others and complies with applicable copyright laws.
        </p>

        <p>
            If you believe that any content available on our platform infringes your copyright, you may submit a request for removal.
        </p>

        <p>
            To process your claim efficiently, please provide:
        </p>

        <ul style="list-style:disc;padding-left:1.5rem" class="d-flex flex-column gap-1">
            <li>Your full name and contact information</li>
            <li>A description of the copyrighted work</li>
            <li>The exact URL(s) of the infringing content</li>
            <li>A statement confirming your ownership or authorization</li>
        </ul>

        <p>
            Send all DMCA notices to:
        </p>

        <div class="d-flex align-items-center gap-1 pt-1">
            <span style="color:#818cf8">✉</span>
            <a href="mailto:contact@aniwaves.ru"
               style="color:#818cf8;font-weight:500">
                contact@aniwaves.ru
            </a>
        </div>

        <p class="pt-1" style="color:#6b7280;font-size:0.875rem">
            We take all reports seriously and will take appropriate action when necessary.
        </p>

    </div>

</div>
@endsection