<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', [
            'reports' => Report::with(['episode.anime', 'user'])->latest()->paginate(20),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        $report->update(['status' => $request->status]);

        return back()->with('success', 'Report updated.');
    }

    public function store(StoreReportRequest $request)
    {
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
