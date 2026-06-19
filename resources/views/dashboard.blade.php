@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-3 py-3" style="max-width:1280px">

    <div class="mb-3">
        <h1 class="fw-semibold" style="color:#fff;font-size:1.5rem">
            Dashboard
        </h1>
        <p class="mt-1" style="color:#9ca3af;font-size:0.875rem">
            Welcome back, {{ auth()->user()->name }}
        </p>
    </div>

    <div style="background:#111827;border:1px solid #374151;border-radius:0.75rem;padding:1.5rem">

        <p style="color:#d1d5db">
            ✅ You are logged in successfully.
        </p>

        <div class="mt-3" style="color:#9ca3af;font-size:0.875rem">
            Explore anime, manage your profile, or continue watching your favorite series.
        </div>

    </div>

</div>
@endsection