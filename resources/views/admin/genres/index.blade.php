@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-semibold text-white">Genres</h1>

        <form action="{{ route('admin.genres.import-from-mal') }}"
              method="POST"
              onsubmit="return confirm('Import all genres from MyAnimeList?')">
            @csrf
            <button type="submit"
                class="btn btn-sm d-flex align-items-center gap-1" style="background:#059669;color:#fff">
                <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import from MAL
            </button>
        </form>
    </div>

    <form action="{{ route('admin.genres.store') }}" method="POST" class="d-flex gap-2 mb-3">
        @csrf
        <input type="text" name="name" placeholder="Genre name"
               class="form-control" style="background:#1f2937;border:1px solid #4b5563;color:#fff;flex:1" required>
        <button type="submit" class="btn" style="background:#4f46e5;color:#fff">Add</button>
    </form>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                    <th class="p-3 text-start">Name</th>
                    <th class="p-3 text-start">Slug</th>
                    <th class="p-3 text-start">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($genres as $genre)
                <tr style="border-bottom:1px solid #374151">
                    <td class="p-3">
                        <form action="{{ route('admin.genres.update', $genre) }}"
                              method="POST" class="d-flex gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $genre->name }}"
                                   class="form-control form-control-sm" style="background:#1f2937;border:1px solid #4b5563;color:#fff">
                            <button type="submit" class="btn btn-sm border-0" style="color:#60a5fa">Save</button>
                        </form>
                    </td>
                    <td class="p-3" style="color:#9ca3af">{{ $genre->slug }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.genres.destroy', $genre) }}"
                              method="POST" onsubmit="return confirm('Delete this genre?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm border-0" style="color:#f87171">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-5 text-center" style="color:#6b7280">
                        <p class="h5" style="color:#d1d5db">No genres found</p>
                        <p class="small mt-1">Add your first genre</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $genres->links() }}
        </div>
    </div>
</div>

@endsection
