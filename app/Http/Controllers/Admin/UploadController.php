<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChunkedUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INITIATE
    |--------------------------------------------------------------------------
    */
    public function initiate(Request $request)
    {
        $data = $request->validate([
            'filename'   => 'required|string|max:255',
            'file_size'  => 'required|integer|min:1',
            'mime_type'  => 'nullable|string|max:100',
            'chunk_size' => 'required|integer|min:1048576|max:52428800',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        try {
            $filename = $this->sanitizeFilename($data['filename']);

            $totalChunks = (int) ceil($data['file_size'] / $data['chunk_size']);

            if ($totalChunks <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid upload size.',
                ], 422);
            }

            $tempDir = 'chunks/' . Str::uuid();

            Storage::disk('local')->makeDirectory($tempDir);

            $upload = ChunkedUpload::create([
                'user_id'         => $user->id,
                'filename'        => $filename,
                'mime_type'       => $data['mime_type'],
                'total_size'      => $data['file_size'],
                'chunk_size'      => $data['chunk_size'],
                'total_chunks'    => $totalChunks,
                'received_chunks' => 0,
                'temp_dir'        => $tempDir,
                'status'          => 'uploading',
            ]);

            return response()->json([
                'success' => true,
                'upload_id' => $upload->id,
                'total_chunks' => $totalChunks,
                'progress' => 0,
            ]);

        } catch (\Throwable $e) {

            $this->logError('Chunk upload initiate failed', $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate upload.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIVE CHUNK
    |--------------------------------------------------------------------------
    */
    public function chunk(Request $request)
    {
        $data = $request->validate([
            'upload_id'   => 'required|exists:chunked_uploads,id',
            'chunk_index' => 'required|integer|min:0',
            'chunk'       => 'required|file',
        ]);

        $upload = ChunkedUpload::findOrFail($data['upload_id']);

        $this->authorizeUploadOwner($request, $upload);

        if ($upload->status !== 'uploading') {
            return response()->json([
                'success' => false,
                'message' => 'Upload closed.',
            ], 400);
        }

        if ($data['chunk_index'] >= $upload->total_chunks) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid chunk index.',
            ], 422);
        }

        try {
            $chunkName = $this->chunkFilename($data['chunk_index']);
            $chunkPath = $upload->temp_dir . '/' . $chunkName;

            // ✅ prevent duplicate upload
            if (!Storage::disk('local')->exists($chunkPath)) {
                $request->file('chunk')->storeAs(
                    $upload->temp_dir,
                    $chunkName,
                    'local'
                );

                $upload->increment('received_chunks');
            }

            $upload->refresh();

            if ($upload->received_chunks >= $upload->total_chunks) {
                $upload->update(['status' => 'assembling']);

                $this->assembleFile($upload);

                $upload->refresh();
            }

            return response()->json([
                'success' => true,
                'progress' => $this->progress($upload),
                'status' => $upload->status,
                'final_path' => $upload->final_path,
                'final_url' => $upload->final_path
                    ? asset('storage/' . $upload->final_path)
                    : null,
            ]);

        } catch (\Throwable $e) {

            $this->logError('Chunk upload failed', $e, [
                'upload_id' => $upload->id,
            ]);

            $upload->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Chunk failed.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ASSEMBLE FILE
    |--------------------------------------------------------------------------
    */
    protected function assembleFile(ChunkedUpload $upload): void
    {
        $local = Storage::disk('local');
        $public = Storage::disk('public');

        try {
            $finalDir = 'uploads/videos/' . date('Y/m/d');
            $public->makeDirectory($finalDir);

            $filename = $this->buildUniqueFilename(
                $upload->filename,
                $finalDir,
                $public
            );

            $finalPath = $finalDir . '/' . $filename;

            $tmp = storage_path('app/' . $upload->temp_dir . '/final.tmp');

            $out = fopen($tmp, 'wb');

            for ($i = 0; $i < $upload->total_chunks; $i++) {
                $chunk = storage_path('app/' . $upload->temp_dir . '/' . $this->chunkFilename($i));

                if (!file_exists($chunk)) {
                    throw new \RuntimeException("Missing chunk {$i}");
                }

                $in = fopen($chunk, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
            }

            fclose($out);

            $stream = fopen($tmp, 'rb');
            $public->writeStream($finalPath, $stream);
            fclose($stream);

            unlink($tmp);
            $local->deleteDirectory($upload->temp_dir);

            $upload->update([
                'status' => 'completed',
                'final_path' => $finalPath,
            ]);

        } catch (\Throwable $e) {

            $this->logError('Chunk assembly failed', $e, [
                'upload_id' => $upload->id,
            ]);

            $upload->update(['status' => 'failed']);
            $this->cleanupUpload($upload);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function authorizeUploadOwner(Request $request, ChunkedUpload $upload): void
    {
        abort_if($upload->user_id !== $request->user()?->id, 403);
    }

    protected function progress(ChunkedUpload $upload): float
    {
        if ($upload->total_chunks <= 0) return 0;

        return round(($upload->received_chunks / $upload->total_chunks) * 100, 1);
    }

    protected function chunkFilename(int $i): string
    {
        return 'chunk_' . str_pad($i, 6, '0', STR_PAD_LEFT);
    }

    protected function cleanupUpload(ChunkedUpload $upload): void
    {
        Storage::disk('local')->deleteDirectory($upload->temp_dir);
    }

    protected function sanitizeFilename(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($filename, PATHINFO_FILENAME));

        if (!$name) $name = 'video';

        return "{$name}.{$ext}";
    }

    protected function buildUniqueFilename(string $filename, string $dir, $disk): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $candidate = $filename;
        $i = 1;

        while ($disk->exists($dir . '/' . $candidate)) {
            $candidate = "{$name}_{$i}.{$ext}";
            $i++;
        }

        return $candidate;
    }
}