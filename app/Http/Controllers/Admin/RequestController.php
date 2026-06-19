<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index (List + Filter + AJAX)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $query = AnimeRequest::with([
                'user:id,name'
            ])
                ->latest();

            // ✅ Filter by status
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            $requests = $query
                ->paginate(20)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX Response
            |--------------------------------------------------------------------------
            */
            if ($request->ajax()) {
                return response()->json([
                    'html' => view('admin.requests._table', compact('requests'))->render(),
                    'pagination' => view('admin.requests._pagination', compact('requests'))->render(),
                    'total' => $requests->total(),
                ]);
            }

            return view('admin.requests.index', compact('requests'));
        } catch (\Throwable $e) {
            Log::error('Anime request index failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load requests.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Request Status
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, AnimeRequest $animeRequest)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,fulfilled,rejected',
        ]);

        try {
            $animeRequest->update([
                'status' => $data['status'],
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request updated successfully.',
                ]);
            }

            return back()->with('success', 'Request updated.');
        } catch (\Throwable $e) {
            Log::error('Anime request update failed', [
                'request_id' => $animeRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update request.');
        }
    }
}
