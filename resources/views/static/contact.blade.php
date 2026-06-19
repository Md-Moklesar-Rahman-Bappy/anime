@extends('layouts.main')

@section('title', 'Contact')

@section('content')
<div class="container" style="max-width:56rem;padding:3rem 1rem">

    <div class="text-center mb-4">
        <h1 class="fw-semibold" style="color:#fff;font-size:1.75rem">
            Contact Us
        </h1>
        <p class="mt-2" style="color:#9ca3af;font-size:0.875rem">
            We'd love to hear from you
        </p>
    </div>

    <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.5rem;color:#9ca3af" class="d-flex flex-column gap-3">

        <p>
            If you have any questions, feedback, or inquiries, feel free to reach out to us.
        </p>

        <div class="d-flex align-items-center gap-2">

            <span style="color:#818cf8;font-size:1.125rem">
                ✉
            </span>

            <a href="mailto:contact@aniwaves.ru"
               style="color:#818cf8;font-weight:500">
                contact@aniwaves.ru
            </a>

        </div>

        <p class="pt-1" style="color:#6b7280;font-size:0.875rem">
            We aim to respond to all inquiries as quickly as possible.
        </p>

    </div>

</div>
@endsection