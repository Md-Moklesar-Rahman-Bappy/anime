<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimeRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index()
    {
        $requests = AnimeRequest::with('user')->latest()->paginate(20);

        return view('admin.requests.index', compact('requests'));
    }

    public function update(Request $request, AnimeRequest $animeRequest)
    {
        $request->validate(['status' => 'required|in:pending,fulfilled,rejected']);
        $animeRequest->update(['status' => $request->status]);

        return back()->with('success', 'Request updated.');
    }
}
