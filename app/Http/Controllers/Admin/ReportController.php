<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['episode.anime', 'user'])->latest()->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function update(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,dismissed',
        ]);

        $report->update(['status' => $request->status]);

        return back()->with('success', 'Report updated.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'episode_id' => 'required|exists:episodes,id',
            'issue_type' => 'required|string|in:broken,audio_not_synced,sub_not_synced,skip_time_wrong,other',
            'description' => 'nullable|string|max:2000',
        ]);

        Report::create([
            'episode_id' => $request->episode_id,
            'user_id' => auth()->id(),
            'issue_type' => $request->issue_type,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'ok']);
    }
}
