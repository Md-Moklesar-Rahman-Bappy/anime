<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['episode.anime', 'user'])
            ->latest();

        // ✅ Optional filtering
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('issue_type', $type);
        }

        $reports = $query->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        try {
            $report->update([
                'status' => $request->status,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Report updated.');
        } catch (\Throwable $e) {
            Log::error('Report update failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update report.');
        }
    }

    public function store(StoreReportRequest $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication required.',
                ], 401);
            }

            Report::create([
                'episode_id' => $request->episode_id,
                'user_id' => auth()->id(),
                'issue_type' => $request->issue_type,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Report submission failed', [
                'episode_id' => $request->episode_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit report.',
            ], 500);
        }
    }
}
