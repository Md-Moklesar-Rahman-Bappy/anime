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
    /*
    |--------------------------------------------------------------------------
    | Initiate Chunked Upload
    |--------------------------------------------------------------------------
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

            if ($totalChunks <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid upload size.',
                ], 422);
            }

            $tempDir = 'chunks/' . Str::uuid()->toString();

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
                'success'       => true,
                'upload_id'     => $upload->id,
                'total_chunks'  => $totalChunks,
                'chunk_size'    => $data['chunk_size'],
                'status'        => $upload->status,
                'received'      => $upload->received_chunks,
                'progress'      => 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk upload initiate failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate upload.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Receive Chunk
    |--------------------------------------------------------------------------
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

        $this->authorizeUploadOwner($upload);

        if ($upload->status !== 'uploading') {
            return response()->json([
                'success' => false,
                'message' => 'Upload is no longer accepting chunks.',
                'status' => $upload->status,
            ], 400);
        }

        if ((int) $data['chunk_index'] >= (int) $upload->total_chunks) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid chunk index.',
            ], 422);
        }

        try {
            $chunkFile = $request->file('chunk');

            $maxExpectedChunkSize = (int) round($upload->chunk_size * 1.10);

            if ($chunkFile->getSize() > $maxExpectedChunkSize) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chunk size exceeds expected size.',
                ], 400);
            }

            $chunkName = $this->chunkFilename((int) $data['chunk_index']);
            $chunkPath = $upload->temp_dir . '/' . $chunkName;

            /*
            |--------------------------------------------------------------------------
            | Duplicate Chunk Handling
            |--------------------------------------------------------------------------
            | If a chunk is already uploaded, return current progress instead of
            | failing hard. This helps when frontend retries same chunk.
            */
            if (Storage::disk('local')->exists($chunkPath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chunk already uploaded.',
                    'received_chunks' => $upload->received_chunks,
                    'total_chunks' => $upload->total_chunks,
                    'progress' => $this->progress($upload),
                    'status' => $upload->status,
                    'final_path' => $upload->status === 'completed' ? $upload->final_path : null,
                    'final_url' => $upload->status === 'completed' && $upload->final_path
                        ? asset('storage/' . $upload->final_path)
                        : null,
                ]);
            }

            $chunkFile->storeAs($upload->temp_dir, $chunkName, 'local');

            $upload->increment('received_chunks');
            $upload->refresh();

            if ((int) $upload->received_chunks >= (int) $upload->total_chunks) {
                $upload->update([
                    'status' => 'assembling',
                ]);

                $this->assembleFile($upload);

                $upload->refresh();
            }

            return response()->json([
                'success' => true,
                'received_chunks' => $upload->received_chunks,
                'total_chunks' => $upload->total_chunks,
                'progress' => $this->progress($upload),
                'status' => $upload->status,
                'final_path' => $upload->status === 'completed' ? $upload->final_path : null,
                'final_url' => $upload->status === 'completed' && $upload->final_path
                    ? asset('storage/' . $upload->final_path)
                    : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk upload failed', [
                'upload_id' => $upload->id,
                'chunk_index' => $data['chunk_index'],
                'error' => $e->getMessage(),
            ]);

            $upload->update([
                'status' => 'failed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload chunk.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Status
    |--------------------------------------------------------------------------
    */
    public function status(Request $request, ChunkedUpload $upload)
    {
        $this->authorizeUploadOwner($upload);

        return response()->json([
            'success' => true,
            'status' => $upload->status,
            'received_chunks' => $upload->received_chunks,
            'total_chunks' => $upload->total_chunks,
            'progress' => $this->progress($upload),
            'final_path' => $upload->status === 'completed' ? $upload->final_path : null,
            'final_url' => $upload->status === 'completed' && $upload->final_path
                ? asset('storage/' . $upload->final_path)
                : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Direct Upload
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file'       => 'required|file|mimes:mp4,webm,mkv,avi,mov|max:102400', // 100MB
            'anime_slug' => 'required|string|max:255',
        ]);

        try {
            $safeSlug = Str::slug($data['anime_slug']) ?: 'anime';

            $path = $request->file('file')->store(
                "anime/{$safeSlug}/videos",
                'public'
            );

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            Log::error('Direct upload failed', [
                'anime_slug' => $data['anime_slug'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Upload
    |--------------------------------------------------------------------------
    */
    public function cancel(ChunkedUpload $upload)
    {
        $this->authorizeUploadOwner($upload);

        try {
            $this->cleanupUpload($upload);

            $upload->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'status' => 'cancelled',
            ]);
        } catch (\Throwable $e) {
            Log::error('Upload cancel failed', [
                'upload_id' => $upload->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel upload.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Assemble Chunks Into Final File
    |--------------------------------------------------------------------------
    */
    protected function assembleFile(ChunkedUpload $upload): void
    {
        $localDisk = Storage::disk('local');
        $publicDisk = Storage::disk('public');

        try {
            $tempDir = $upload->temp_dir;

            if (!$tempDir || !$localDisk->exists($tempDir)) {
                throw new \RuntimeException('Temporary chunk directory does not exist.');
            }

            $finalDir = 'uploads/videos/' . date('Y/m/d');

            $publicDisk->makeDirectory($finalDir);

            $filename = $this->buildUniqueFilename(
                $upload->filename,
                $finalDir,
                $publicDisk
            );

            $finalPath = $finalDir . '/' . $filename;

            $assembledTempPath = storage_path('app/' . $tempDir . '/_assembled.tmp');

            $outputHandle = fopen($assembledTempPath, 'wb');

            if (!$outputHandle) {
                throw new \RuntimeException('Unable to open output file for assembly.');
            }

            for ($i = 0; $i < $upload->total_chunks; $i++) {
                $chunkAbsolutePath = storage_path(
                    'app/' . $tempDir . '/' . $this->chunkFilename($i)
                );

                if (!file_exists($chunkAbsolutePath)) {
                    fclose($outputHandle);
                    throw new \RuntimeException("Missing chunk: {$i}");
                }

                $chunkHandle = fopen($chunkAbsolutePath, 'rb');

                if (!$chunkHandle) {
                    fclose($outputHandle);
                    throw new \RuntimeException("Unable to open chunk: {$i}");
                }

                stream_copy_to_stream($chunkHandle, $outputHandle);

                fclose($chunkHandle);
                @unlink($chunkAbsolutePath);
            }

            fclose($outputHandle);

            $stream = fopen($assembledTempPath, 'rb');

            if (!$stream) {
                throw new \RuntimeException('Unable to reopen assembled file.');
            }

            $publicDisk->writeStream($finalPath, $stream);

            fclose($stream);

            @unlink($assembledTempPath);

            $localDisk->deleteDirectory($tempDir);

            $upload->update([
                'status' => 'completed',
                'final_path' => $finalPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chunk assembly failed', [
                'upload_id' => $upload->id,
                'temp_dir' => $upload->temp_dir,
                'error' => $e->getMessage(),
            ]);

            $this->cleanupUpload($upload);

            $upload->update([
                'status' => 'failed',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    protected function authorizeUploadOwner(ChunkedUpload $upload): void
    {
        abort_if((int) $upload->user_id !== (int) auth()->id(), 403);
    }

    protected function progress(ChunkedUpload $upload): float
    {
        if ((int) $upload->total_chunks <= 0) {
            return 0;
        }

        return round(
            ((int) $upload->received_chunks / (int) $upload->total_chunks) * 100,
            1
        );
    }

    protected function chunkFilename(int $index): string
    {
        return 'chunk_' . str_pad((string) $index, 6, '0', STR_PAD_LEFT);
    }

    protected function cleanupUpload(ChunkedUpload $upload): void
    {
        $disk = Storage::disk('local');

        if ($upload->temp_dir && $disk->exists($upload->temp_dir)) {
            $disk->deleteDirectory($upload->temp_dir);
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        $safeName = trim((string) $safeName, '._-');

        if ($safeName === '') {
            $safeName = 'video';
        }

        $allowedExtensions = [
            'mp4',
            'webm',
            'mkv',
            'avi',
            'mov',
        ];

        if (!in_array($extension, $allowedExtensions, true)) {
            $extension = 'mp4';
        }

        return "{$safeName}.{$extension}";
    }

    protected function buildUniqueFilename(string $filename, string $directory, $disk): string
    {
        $filename = $this->sanitizeFilename($filename);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $candidate = $filename;
        $counter = 1;

        while ($disk->exists($directory . '/' . $candidate)) {
            $candidate = "{$name}_{$counter}.{$extension}";
            $counter++;
        }

        return $candidate;
    }
}
