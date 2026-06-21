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
    | INDEX (AJAX + FILTER + SEARCH + LIVE SUPPORT)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');
            $search = trim((string) $request->input('search'));

            $query = Report::query()
                ->with([
                    'episode:id,anime_id,number',
                    'episode.anime:id,title',
                    'user:id,name'
                ])
                ->latest();

            /*
            |--------------------------------------------------------------------------
            | FILTER: STATUS
            |--------------------------------------------------------------------------
            */
            if ($status && in_array($status, ['pending', 'resolved', 'dismissed'])) {
                $query->where('status', $status);
            }

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */
            if ($search !== '') {
                $safe = '%' . addcslashes($search, '%_') . '%';

                $query->where(function ($q) use ($safe) {
                    $q->where('issue_type', 'like', $safe)
                        ->orWhere('description', 'like', $safe)
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', $safe))
                        ->orWhereHas('episode.anime', fn($a) => $a->where('title', 'like', $safe));
                });
            }

            $reports = $query->paginate(15)->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX RESPONSE
            |--------------------------------------------------------------------------
            */
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.reports._list', compact('reports'))->render(),
                    'url'  => $request->fullUrl(),
                ]);
            }

            return view('admin.reports.index', compact('reports'));
        } catch (\Throwable $e) {

            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load reports.',
                ], 500);
            }

            return back()->with('error', 'Failed to load reports.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS (AJAX READY)
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

            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update report.',
                ], 500);
            }

            return back()->with('error', 'Failed to update report.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (FRONTEND REPORT)
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
                'description' => $request->input('description'),
                'status'     => 'pending',
            ]);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Report submitted successfully.',
            ]);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit report.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK RESOLVE (PRO FEATURE)
    |--------------------------------------------------------------------------
    */
    public function bulkResolve(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (!is_array($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reports selected.',
                ], 422);
            }

            Report::whereIn('id', $ids)
                ->where('status', 'pending')
                ->update(['status' => 'resolved']);

            return response()->json([
                'success' => true,
                'message' => 'Selected reports resolved.',
            ]);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Bulk resolve failed.',
            ], 500);
        }
    }
}
