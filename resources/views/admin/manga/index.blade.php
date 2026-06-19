@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-semibold text-white">Manga</h1>
        <a href="{{ route('admin.manga.create') }}"
           class="btn btn-sm" style="background:#059669;color:#fff">Add Manga</a>
    </div>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                    <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                        <th class="p-3 text-start">Title</th>
                        <th class="p-3 text-start">Type</th>
                        <th class="p-3 text-start">Status</th>
                        <th class="p-3 text-start">Chapters</th>
                        <th class="p-3 text-start">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mangaList as $manga)
                    <tr style="border-bottom:1px solid #374151">
                        <td class="p-3 text-white fw-medium">{{ $manga->title }}</td>
                        <td class="p-3" style="color:#9ca3af">{{ $manga->type ?? '—' }}</td>
                        <td class="p-3">
                            <span class="badge rounded-1 fw-normal" style="font-size:0.75rem;
                                @switch($manga->status)
                                    @case('Completed') background:rgba(34,197,94,0.1);color:#4ade80 @break
                                    @case('Ongoing') background:rgba(99,102,241,0.1);color:#818cf8 @break
                                    @case('Hiatus') background:rgba(234,179,8,0.1);color:#facc15 @break
                                    @case('Cancelled') background:rgba(239,68,68,0.1);color:#f87171 @break
                                    @default background:#374151;color:#9ca3af
                                @endswitch
                            ">{{ $manga->status ?? 'N/A' }}</span>
                        </td>
                        <td class="p-3" style="color:#9ca3af">{{ $manga->chapters_count ?? 0 }}</td>
                        <td class="p-3">
                            <div class="d-flex gap-3 small">
                                <a href="{{ route('admin.manga.chapters.index', $manga) }}" style="color:#818cf8">Chapters</a>
                                <a href="{{ route('admin.manga.edit', $manga) }}" style="color:#60a5fa">Edit</a>
                                <form action="{{ route('admin.manga.destroy', $manga) }}"
                                      method="POST" onsubmit="return confirm('Delete this manga?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm border-0 p-0" style="color:#f87171">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-5 text-center" style="color:#6b7280">
                            <p class="h5" style="color:#d1d5db">No manga found</p>
                            <p class="small mt-1">Add your first manga</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $mangaList->links() }}
        </div>
    </div>

</div>
@endsection