@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">

    <h1 class="h4 fw-semibold text-white mb-3">Requests</h1>

    <div class="card" style="background:#111827;border:1px solid #374151;border-radius:1rem;overflow:hidden">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0 align-middle">
                <thead>
                    <tr style="background:#0f172a;color:#9ca3af;border-bottom:1px solid #374151">
                        <th class="p-3 text-start">Anime</th>
                        <th class="p-3 text-start">User</th>
                        <th class="p-3 text-start">Description</th>
                        <th class="p-3 text-start">Status</th>
                        <th class="p-3 text-start">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr style="border-bottom:1px solid #374151">
                        <td class="p-3 text-white">{{ $req->anime_title }}</td>
                        <td class="p-3" style="color:#d1d5db">{{ $req->user->name }}</td>
                        <td class="p-3" style="color:#9ca3af">{{ Str::limit($req->description, 60) }}</td>
                        <td class="p-3">
                            <span class="badge rounded-1 fw-normal" style="font-size:0.75rem;
                                @switch($req->status)
                                    @case('pending') background:rgba(234,179,8,0.1);color:#facc15 @break
                                    @case('fulfilled') background:rgba(34,197,94,0.1);color:#4ade80 @break
                                    @case('rejected') background:rgba(239,68,68,0.1);color:#f87171 @break
                                    @default background:#374151;color:#9ca3af
                                @endswitch
                            ">{{ ucfirst($req->status) }}</span>
                        </td>
                        <td class="p-3">
                            <form action="{{ route('admin.requests.update', $req) }}" method="POST">
                                @csrf @method('PUT')
                                <select name="status" onchange="this.form.submit()"
                                    class="form-select form-select-sm" style="background:#1f2937;color:#fff;border:1px solid #4b5563">
                                    <option value="pending" @selected($req->status=='pending')>Pending</option>
                                    <option value="fulfilled" @selected($req->status=='fulfilled')>Fulfilled</option>
                                    <option value="rejected" @selected($req->status=='rejected')>Rejected</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-5 text-center" style="color:#6b7280">
                            <p class="h5" style="color:#d1d5db">No requests found</p>
                            <p class="small mt-1">User requests will appear here</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3" style="border-top:1px solid #374151">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection