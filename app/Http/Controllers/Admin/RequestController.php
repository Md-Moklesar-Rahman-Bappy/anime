<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX - LIVE / AJAX / SEARCH / FILTER
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');
            $search = trim((string) $request->input('search'));

            $query = AnimeRequest::query()
                ->with('user:id,name')
                ->latest();

            /*
            |--------------------------------------------------------------------------
            | FILTER BY STATUS
            |--------------------------------------------------------------------------
            */
            if ($status && in_array($status, ['pending', 'fulfilled', 'rejected'], true)) {
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
                    $q->where('anime_title', 'like', $safe)
                        ->orWhere('description', 'like', $safe)
                        ->orWhereHas('user', function ($userQuery) use ($safe) {
                            $userQuery->where('name', 'like', $safe);
                        });
                });
            }

            $requests = $query->paginate(15)->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX RESPONSE
            |--------------------------------------------------------------------------
            */
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.requests._list', compact('requests'))->render(),
                    'url'  => $request->fullUrl(),
                ]);
            }

            return view('admin.requests.index', compact('requests'));
        } catch (\Throwable $e) {
            Log::error('Admin requests index failed', [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load requests.',
                ], 500);
            }

            return back()->with('error', 'Failed to load requests.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, AnimeRequest $animeRequest)
    {
        try {
            $data = $request->validate([
                'status' => [
                    'required',
                    Rule::in(['pending', 'fulfilled', 'rejected']),
                ],
            ]);

            $animeRequest->update([
                'status' => $data['status'],
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request status updated.',
                    'request' => $animeRequest->fresh(),
                ]);
            }

            return back()->with('success', 'Request status updated.');
        } catch (\Throwable $e) {
            Log::error('Request update failed', [
                'request_id' => $animeRequest->id,
                'error'      => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update request.',
                ], 500);
            }

            return back()->with('error', 'Failed to update request.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK FULFILL VISIBLE PENDING REQUESTS
    |--------------------------------------------------------------------------
    */
    public function bulkFulfill(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (!is_array($ids) || count($ids) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No requests selected.',
                ], 422);
            }

            AnimeRequest::whereIn('id', $ids)
                ->where('status', 'pending')
                ->update(['status' => 'fulfilled']);

            return response()->json([
                'success' => true,
                'message' => 'Visible pending requests marked as fulfilled.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk fulfill requests failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update requests.',
            ], 500);
        }
    }
}
