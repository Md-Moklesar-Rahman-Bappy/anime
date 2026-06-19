@extends('admin.layouts.app')

@section('title', 'Featured Slider')

@section('content')
<div class="container" style="max-width:900px">

    <h1 class="h4 fw-semibold text-white mb-3">Featured Slider</h1>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem">

        <div class="card-body">
            <h2 class="h5 fw-medium text-white mb-2">Auto Fill</h2>
            <p class="small mb-4" style="color:#9ca3af">
                Populate the homepage slider automatically from your anime list.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                    @csrf
                    <input type="hidden" name="mode" value="top_rated">
                    <input type="hidden" name="count" value="5">
                    <button class="btn btn-sm" style="background:#ca8a04;color:#fff;font-weight:500">Top Rated</button>
                </form>
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                    @csrf
                    <input type="hidden" name="mode" value="most_viewed">
                    <input type="hidden" name="count" value="5">
                    <button class="btn btn-sm" style="background:#4f46e5;color:#fff;font-weight:500">Most Popular</button>
                </form>
                <form method="POST" action="{{ route('admin.featured.auto-fill') }}">
                    @csrf
                    <input type="hidden" name="mode" value="recent">
                    <input type="hidden" name="count" value="5">
                    <button class="btn btn-sm" style="background:#059669;color:#fff;font-weight:500">Recent Uploads</button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
