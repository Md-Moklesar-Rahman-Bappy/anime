<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimeRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (LIST + FILTER + AJAX)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');

            $query = AnimeRequest::with([
                    'user:id,name'
                ])
                ->select(
                    'id',
                    'user_id',
                    'title',
                    'status',
                    'created_at'
                )
                ->latest();

            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            */
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $requests = $query
                ->paginate(20)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | AJAX RESPONSE
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

            $this->logError('Anime request index failed', $e);

            return $this->redirectError('Failed to load requests.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
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

            /*
            |--------------------------------------------------------------------------
            | AJAX RESPONSE
            |--------------------------------------------------------------------------
            */
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request updated successfully.',
                ]);
            }

            return back()->with('success', 'Request updated successfully.');

        } catch (\Throwable $e) {

            $this->logError('Anime request update failed', $e, [
                'request_id' => $animeRequest->id,
            ]);

            return $this->redirectError('Failed to update request.');
        }
    }
}