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
                return $this->sendResponse($request, false, 'Please login to comment.', 401);
            }

            /*
            |--------------------------------------------------------------------------
            | Anti Spam (Session Based)
            |--------------------------------------------------------------------------
            */
            $lastCommentTime = session('last_comment_time');

            if ($lastCommentTime && (time() - $lastCommentTime < 30)) {
                return $this->sendResponse(
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
                'user_id' => $user->id,
                'body' => trim($request->body),
                'status' => Comment::STATUS_VISIBLE,
            ]);

            session()->put('last_comment_time', time());

            return $this->sendResponse($request, true, 'Comment posted.', 200, [
                'comment' => $comment->load('user:id,name'),
            ]);
        } catch (\Throwable $e) {

            Log::error('Comment create failed', [
                'user_id' => $request->user()?->id,
                'episode_id' => $request->episode_id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendResponse(
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
                abort(403);
            }

            /*
            |--------------------------------------------------------------------------
            | Authorization
            |--------------------------------------------------------------------------
            */
            if (
                $comment->user_id !== $user->id &&
                !$user->isAdmin()
            ) {
                abort(403);
            }

            $comment->delete();

            return $this->sendResponse(
                $request,
                true,
                'Comment deleted.',
                200
            );
        } catch (\Throwable $e) {

            Log::error('Comment delete failed', [
                'comment_id' => $comment->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendResponse(
                $request,
                false,
                'Failed to delete comment.',
                500
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE HELPER (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */

    private function sendResponse(Request $request, bool $success, string $message, int $status, array $data = [])
    {
        if ($request->wantsJson()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $data), $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
