<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMangaCommentRequest;
use App\Models\MangaComment;
use Illuminate\Http\Request;

class MangaCommentsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT
    |--------------------------------------------------------------------------
    */

    public function store(StoreMangaCommentRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->response(
                    $request,
                    false,
                    'Please login to comment.',
                    401
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Anti-Spam (Session Based)
            |--------------------------------------------------------------------------
            */
            $lastCommentTime = session('last_manga_comment_time');

            if ($lastCommentTime && (time() - $lastCommentTime < 30)) {
                return $this->response(
                    $request,
                    false,
                    'Please wait before posting.',
                    429
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Comment
            |--------------------------------------------------------------------------
            */
            $comment = MangaComment::create([
                'chapter_id' => $request->chapter_id,
                'user_id' => $user->id,
                'body' => trim($request->body),
                'status' => MangaComment::STATUS_VISIBLE,
            ]);

            session()->put('last_manga_comment_time', time());

            return $this->response($request, true, 'Comment posted.', 200, [
                'comment' => $comment->load('user:id,name'),
            ]);
        } catch (\Throwable $e) {

            $this->logError('Manga comment create failed', $e, [
                'user_id' => $request->user()?->id,
                'chapter_id' => $request->chapter_id,
            ]);

            return $this->response(
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

    public function destroy(Request $request, MangaComment $mangaComment)
    {
        try {
            $user = $request->user();

            if (!$user) {
                abort(403);
            }

            /*
            |--------------------------------------------------------------------------
            | Authorization (owner or admin)
            |--------------------------------------------------------------------------
            */
            if (
                $mangaComment->user_id !== $user->id &&
                !$user->isAdmin()
            ) {
                abort(403);
            }

            $mangaComment->delete();

            return $this->response(
                $request,
                true,
                'Comment deleted.',
                200
            );
        } catch (\Throwable $e) {

            $this->logError('Manga comment delete failed', $e, [
                'comment_id' => $mangaComment->id,
                'user_id' => $request->user()?->id,
            ]);

            return $this->response(
                $request,
                false,
                'Delete failed.',
                500
            );
        }
    }
}
