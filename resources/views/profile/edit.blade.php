@extends('layouts.main')

@section('title', 'Profile')

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="mb-3">
        <h1 class="fw-semibold" style="color:#fff;font-size:1.5rem">
            Profile Settings
        </h1>
        <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
            Manage your account information and security
        </p>
    </div>

    <div class="d-flex flex-column gap-3">

        <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.1)">
            <div style="max-width:36rem">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.1)">
            <div style="max-width:36rem">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div style="background:#111827;border:1px solid rgba(239,68,68,0.1);border-radius:0.75rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.1)">
            <div style="max-width:36rem">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>

</div>
@endsection