<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChunkedUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Start a new chunked upload session.
     */
    public function initiate(Request $request)
    {
        $data = $request->validate([
            'filename'   => 'required|string|max:255',
            'file_size'  => 'required|integer|min:1',
            'mime_type'  => 'nullable|string|max:100',
            'chunk_size' => 'required|integer|min:1048576|max:52428800', // 1MB - 50MB
        ]);

        try {
            $filename = $this->sanitizeFilename($data['filename']);
            $totalChunks = (int) ceil($data['file_size'] / $data['chunk_size']);
            $tempDir = 'chunks/' . uniqid('upload_', true) . '_' . time();

            Storage::disk('local')->makeDirectory($tempDir);

            $upload = ChunkedUpload::create([
                'user_id'         => auth()->id(),
                'filename'        => $filename,
                'mime_type'       => $data['mime_type'] ?? null,
                'total_size'      => $data['file_size'],
                'chunk_size'      => $data['chunk_size'],
                'total_chunks'    => $totalChunks,
                'received_chunks' => 0,
                'temp_dir'        => $tempDir,
                'status'          => 'uploading',
                'final_path'      => null,
            ]);

            return response()->json([
                'upload_id'    => $upload->id,
                'total_chunks' => $totalChunks,
                'chunk_size'   => $data['chunk_size'],
                'status'       => $upload->status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk upload initiate failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to initiate upload.',
            ], 500);
        }
    }

    /**
     * Receive a single chunk.
     */
    public function chunk(Request $request)
    {
        $data = $request->validate([
            'upload_id'   => 'required|exists:chunked_uploads,id',
            'chunk_index' => 'required|integer|min:0',
            'chunk'       => 'required|file',
        ]);

        /** @var ChunkedUpload $upload */
        $upload = ChunkedUpload::findOrFail($data['upload_id']);

        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        if ($upload->status !== 'uploading') {
            return response()->json([
                'error' => 'Upload is no longer accepting chunks.',
            ], 400);
        }

        if ($data['chunk_index'] >= $upload->total_chunks) {
            return response()->json([
                'error' => 'Invalid chunk index.',
            ], 422);
        }

        try {
            $chunkFile = $request->file('chunk');

            // Extra size protection
            $maxExpectedChunkSize = (int) round($upload->chunk_size * 1.10);
            if ($chunkFile->getSize() > $maxExpectedChunkSize) {
                return response()->json([
                    'error' => 'Chunk size exceeds expected size.',
                ], 400);
            }

            $chunkName = 'chunk_' . str_pad((string) $data['chunk_index'], 6, '0', STR_PAD_LEFT);
            $chunkPath = $upload->temp_dir . '/' . $chunkName;

            // Prevent duplicate/replayed chunk uploads
            if (Storage::disk('local')->exists($chunkPath)) {
                return response()->json([
                    'error' => 'Chunk already uploaded.',
                ], 409);
            }

            $chunkFile->storeAs($upload->temp_dir, $chunkName, 'local');

            $upload->increment('received_chunks');
            $upload->refresh();

            if ($upload->received_chunks >= $upload->total_chunks) {
                $upload->update(['status' => 'assembling']);
                $this->assembleFile($upload);
                $upload->refresh();
            }

            $progress = $upload->total_chunks > 0
                ? round(($upload->received_chunks / $upload->total_chunks) * 100, 1)
                : 0;

            return response()->json([
                'received_chunks' => $upload->received_chunks,
                'total_chunks'    => $upload->total_chunks,
                'progress'        => $progress,
                'status'          => $upload->status,
                'final_path'      => $upload->status === 'completed' ? $upload->final_path : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk upload failed', [
                'upload_id'   => $upload->id,
                'chunk_index' => $data['chunk_index'],
                'error'       => $e->getMessage(),
            ]);

            $upload->update(['status' => 'failed']);

            return response()->json([
                'error' => 'Failed to upload chunk.',
            ], 500);
        }
    }

    /**
     * Get upload status.
     */
    public function status(Request $request, ChunkedUpload $upload)
    {
        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        $progress = $upload->total_chunks > 0
            ? round(($upload->received_chunks / $upload->total_chunks) * 100, 1)
            : 0;

        return response()->json([
            'status'          => $upload->status,
            'received_chunks' => $upload->received_chunks,
            'total_chunks'    => $upload->total_chunks,
            'progress'        => $progress,
            'final_path'      => $upload->status === 'completed' ? $upload->final_path : null,
        ]);
    }

    /**
     * Assemble all chunks into one final file.
     */
    protected function assembleFile(ChunkedUpload $upload): void
    {
        $disk = Storage::disk('local');

        try {
            $tempDir = $upload->temp_dir;

            if (!$disk->exists($tempDir)) {
                throw new \RuntimeException('Temporary chunk directory does not exist.');
            }

            $finalDir = 'uploads/videos/' . date('Y/m/d');
            $filename = $this->buildUniqueFilename($upload->filename, $finalDir, $disk);
            $finalPath = $finalDir . '/' . $filename;

            $disk->makeDirectory($finalDir);

            $assembledTempPath = storage_path('app/' . $tempDir . '/_assembled');
            $outputHandle = fopen($assembledTempPath, 'wb');

            if (!$outputHandle) {
                throw new \RuntimeException('Unable to open output file for assembly.');
            }

            for ($i = 0; $i < $upload->total_chunks; $i++) {
                $chunkPath = storage_path(
                    'app/' . $tempDir . '/chunk_' . str_pad((string) $i, 6, '0', STR_PAD_LEFT)
                );

                if (!file_exists($chunkPath)) {
                    fclose($outputHandle);
                    throw new \RuntimeException("Missing chunk: {$i}");
                }

                $chunkHandle = fopen($chunkPath, 'rb');

                if (!$chunkHandle) {
                    fclose($outputHandle);
                    throw new \RuntimeException("Unable to open chunk: {$i}");
                }

                stream_copy_to_stream($chunkHandle, $outputHandle);

                fclose($chunkHandle);
                @unlink($chunkPath);
            }

            fclose($outputHandle);

            $stream = fopen($assembledTempPath, 'rb');
            if (!$stream) {
                throw new \RuntimeException('Unable to reopen assembled file.');
            }

            $disk->writeStream($finalPath, $stream);
            fclose($stream);

            @unlink($assembledTempPath);

            // Cleanup temp directory
            $disk->deleteDirectory($tempDir);

            $upload->update([
                'status'     => 'completed',
                'final_path' => $finalPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk assembly failed', [
                'upload_id' => $upload->id,
                'temp_dir'  => $upload->temp_dir,
                'error'     => $e->getMessage(),
            ]);

            $this->cleanupUpload($upload);

            $upload->update([
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Simple direct upload (non-chunked).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file'       => 'required|file|mimes:mp4,webm,mkv,avi,mov|max:102400', // 100MB
            'anime_slug' => 'required|string|max:255',
        ]);

        try {
            $safeSlug = Str::slug($data['anime_slug']);
            $path = $request->file('file')->store("anime/{$safeSlug}/videos", 'public');

            return response()->json([
                'path' => $path,
                'url'  => url('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            Log::error('Direct upload failed', [
                'anime_slug' => $data['anime_slug'],
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Upload failed.',
            ], 500);
        }
    }

    /**
     * Cancel an active upload.
     */
    public function cancel(ChunkedUpload $upload)
    {
        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->cleanupUpload($upload);

            $upload->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'status' => 'cancelled',
            ]);
        } catch (\Throwable $e) {
            Log::error('Upload cancel failed', [
                'upload_id' => $upload->id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to cancel upload.',
            ], 500);
        }
    }

    /**
     * Delete upload temp directory.
     */
    protected function cleanupUpload(ChunkedUpload $upload): void
    {
        $disk = Storage::disk('local');

        if ($upload->temp_dir && $disk->exists($upload->temp_dir)) {
            $disk->deleteDirectory($upload->temp_dir);
        }
    }

    /**
     * Sanitize a user-provided filename.
     */
    protected function sanitizeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        $safeName = trim($safeName, '._-');
        $safeName = $safeName !== '' ? $safeName : 'video';

        return $extension
            ? $safeName . '.' . strtolower($extension)
            : $safeName;
    }

    /**
     * Ensure final filename is unique inside target directory.
     */
    protected function buildUniqueFilename(string $filename, string $directory, $disk): string
    {
        $filename = $this->sanitizeFilename($filename);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $candidate = $filename;
        $counter = 1;

        while ($disk->exists($directory . '/' . $candidate)) {
            $candidate = $extension
                ? "{$name}_{$counter}.{$extension}"
                : "{$name}_{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
