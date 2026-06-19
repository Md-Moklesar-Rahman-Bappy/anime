<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReportRequest;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST + FILTERS)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');
            $type   = $request->input('type');

            $query = Report::with([
                    'episode.anime:id,title,slug',
                    'user:id,name'
                ])
                ->select('id', 'episode_id', 'user_id', 'issue_type', 'status', 'created_at')
                ->latest();

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */
            if (!empty($status)) {
                $query->where('status', $status);
            }

            if (!empty($type)) {
                $query->where('issue_type', $type);
            }

            $reports = $query
                ->paginate(20)
                ->withQueryString();

            return view('admin.reports.index', compact('reports'));

        } catch (\Throwable $e) {

            $this->logError('Report index failed', $e);

            return $this->redirectError('Failed to load reports.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE REPORT STATUS
    |--------------------------------------------------------------------------
    */
    public function update(UpdateReportRequest $request, Report $report)
    {
        try {
            $report->update([
                'status' => $request->input('status'),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report updated successfully.',
                ]);
            }

            return back()->with('success', 'Report updated successfully.');

        } catch (\Throwable $e) {

            $this->logError('Report update failed', $e, [
                'report_id' => $report->id,
            ]);

            return $this->redirectError('Failed to update report.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE REPORT (FRONTEND)
    |--------------------------------------------------------------------------
    */
    public function store(StoreReportRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication required.',
                ], 401);
            }

            Report::create([
                'episode_id' => $request->input('episode_id'),
                'user_id'    => $user->id,
                'issue_type' => $request->input('issue_type'),
                'description'=> $request->input('description'),
                'status'     => 'pending',
            ]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Report submitted successfully.',
            ]);

        } catch (\Throwable $e) {

            $this->logError('Report submission failed', $e, [
                'episode_id' => $request->input('episode_id'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit report.',
            ], 500);
        }
    }
}