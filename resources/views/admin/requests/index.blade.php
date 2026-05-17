@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Requests</h1>
    <div class="bg-gray-900 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-gray-400 border-b border-gray-800"><th>Anime</th><th>User</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($requests as $req)
                <tr class="border-b border-gray-800">
                    <td class="p-3">{{ $req->anime_title }}</td>
                    <td class="p-3">{{ $req->user->name }}</td>
                    <td class="p-3 text-gray-400">{{ Str::limit($req->description, 50) }}</td>
                    <td class="p-3">{{ $req->status }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.requests.update', $req) }}" method="POST">
                            @csrf @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="bg-gray-800 text-white rounded px-2 py-1 text-sm border border-gray-700">
                                <option value="pending" @selected($req->status=='pending')>Pending</option>
                                <option value="fulfilled" @selected($req->status=='fulfilled')>Fulfilled</option>
                                <option value="rejected" @selected($req->status=='rejected')>Rejected</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</div>
@endsection
