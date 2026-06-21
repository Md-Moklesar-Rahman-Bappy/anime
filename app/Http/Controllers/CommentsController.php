<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT
    |--------------------------------------------------------------------------
    */
    public function store(StoreCommentRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respond($request, false, 'Please login to comment.', 401);
            }

            /*
            |--------------------------------------------------------------------------
            | Anti-Spam Protection (30s delay)
            |--------------------------------------------------------------------------
            */
            $lastCommentTime = session('last_comment_time');

            if ($lastCommentTime && (time() - $lastCommentTime < 30)) {
                return $this->respond(
                    $request,
                    false,
                    'Please wait before posting another comment.',
                    429
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Comment
            |--------------------------------------------------------------------------
            */
            $comment = Comment::create([
                'episode_id' => $request->episode_id,
                'user_id'    => $user->id,
                'body'       => trim($request->body),
                'status'     => Comment::STATUS_VISIBLE,
            ]);

            // ✅ Store timestamp
            session()->put('last_comment_time', time());

            return $this->respond($request, true, 'Comment posted.', 200, [
                'comment' => $comment->load('user:id,name'),
            ]);
        } catch (\Throwable $e) {

            Log::error('Comment create failed', [
                'user_id'    => $request->user()?->id,
                'episode_id' => $request->episode_id,
                'error'      => $e->getMessage(),
            ]);

            return $this->respond(
                $request,
                false,
                'Failed to post comment.',
                500
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE COMMENT
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Comment $comment)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respond($request, false, 'Unauthorized.', 403);
            }

            /*
            |--------------------------------------------------------------------------
            | Authorization
            |--------------------------------------------------------------------------
            */
            if ($comment->user_id !== $user->id && !$user->isAdmin()) {
                return $this->respond($request, false, 'Unauthorized.', 403);
            }

            $comment->delete();

            return $this->respond($request, true, 'Comment deleted.', 200);
        } catch (\Throwable $e) {

            Log::error('Comment delete failed', [
                'comment_id' => $comment->id,
                'user_id'    => $request->user()?->id,
                'error'      => $e->getMessage(),
            ]);

            return $this->respond(
                $request,
                false,
                'Failed to delete comment.',
                500
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL: FETCH LATEST COMMENTS (FOR LIVE SYSTEM)
    |--------------------------------------------------------------------------
    */
    public function latest(Request $request)
    {
        try {
            $comments = Comment::latest()
                ->with('user:id,name')
                ->take(20)
                ->get();

            return $this->respond($request, true, 'Comments loaded.', 200, [
                'comments' => $comments,
            ]);
        } catch (\Throwable $e) {

            Log::error('Fetch comments failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->respond(
                $request,
                false,
                'Failed to load comments.',
                500
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE HELPER (STANDARDIZED)
    |--------------------------------------------------------------------------
    */
    private function respond(Request $request, bool $success, string $message, int $status, array $data = [])
    {
        // ✅ AJAX / API response
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $data), $status);
        }

        // ✅ Blade response
        return back()->with($success ? 'success' : 'error', $message);
    }
}
