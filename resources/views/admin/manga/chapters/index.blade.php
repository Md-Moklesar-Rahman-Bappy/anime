@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-semibold text-white">Chapters: {{ $manga->title }}</h1>
            <a href="{{ route('admin.manga.index') }}" class="small" style="color:#6b7280">← Back to Manga</a>
        </div>
        <a href="{{ route('admin.manga.chapters.create', $manga) }}"
           class="btn btn-sm" style="background:#059669;color:#fff">Add Chapter</a>
    </div>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                    <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                        <th class="p-3 text-start">#</th>
                        <th class="p-3 text-start">Title</th>
                        <th class="p-3 text-start">Pages</th>
                        <th class="p-3 text-start">Created</th>
                        <th class="p-3 text-start">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chapters as $chapter)
                    <tr style="border-bottom:1px solid #374151">
                        <td class="p-3 fw-semibold" style="color:#818cf8">Ch. {{ rtrim(rtrim($chapter->number, '0'), '.') }}</td>
                        <td class="p-3" style="color:#d1d5db">{{ $chapter->title ?? 'Untitled Chapter' }}</td>
                        <td class="p-3" style="color:#9ca3af">{{ $chapter->pages_count }}</td>
                        <td class="p-3 small" style="color:#6b7280">{{ $chapter->created_at->format('Y-m-d') }}</td>
                        <td class="p-3">
                            <div class="d-flex gap-3 small">
                                <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}" style="color:#60a5fa">Edit</a>
                                <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}"
                                      method="POST" onsubmit="return confirm('Delete this chapter and all its pages?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm border-0 p-0" style="color:#f87171">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-5 text-center" style="color:#6b7280">
                            <p class="h5" style="color:#d1d5db">No chapters yet</p>
                            <p class="small mt-1">Create your first chapter</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $chapters->links() }}
        </div>
    </div>

</div>
@endsection