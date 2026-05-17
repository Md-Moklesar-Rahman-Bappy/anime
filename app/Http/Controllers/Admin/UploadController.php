<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChunkedUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function initiate(Request $request)
    {
        $data = $request->validate([
            'filename' => 'required|string',
            'file_size' => 'required|integer',
            'mime_type' => 'nullable|string',
            'chunk_size' => 'required|integer|min:1048576|max:52428800',
        ]);

        $totalChunks = (int) ceil($data['file_size'] / $data['chunk_size']);
        $tempDir = 'chunks/'.uniqid('upload_').'_'.time();

        Storage::disk('local')->makeDirectory($tempDir);

        $upload = ChunkedUpload::create([
            'user_id' => auth()->id(),
            'filename' => $data['filename'],
            'mime_type' => $data['mime_type'],
            'total_size' => $data['file_size'],
            'chunk_size' => $data['chunk_size'],
            'total_chunks' => $totalChunks,
            'received_chunks' => 0,
            'temp_dir' => $tempDir,
            'status' => 'uploading',
        ]);

        return response()->json([
            'upload_id' => $upload->id,
            'total_chunks' => $totalChunks,
            'chunk_size' => $data['chunk_size'],
        ]);
    }

    public function chunk(Request $request)
    {
        $data = $request->validate([
            'upload_id' => 'required|exists:chunked_uploads,id',
            'chunk_index' => 'required|integer|min:0',
            'chunk' => 'required|file',
        ]);

        $upload = ChunkedUpload::findOrFail($data['upload_id']);

        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        if ($upload->status !== 'uploading') {
            return response()->json(['error' => 'Upload already completed or failed.'], 400);
        }

        $chunkPath = $upload->temp_dir.'/chunk_'.str_pad($data['chunk_index'], 6, '0', STR_PAD_LEFT);
        $data['chunk']->storeAs($upload->temp_dir, basename($chunkPath), 'local');

        $upload->increment('received_chunks');

        if ($upload->received_chunks >= $upload->total_chunks) {
            $upload->update(['status' => 'assembling']);
            $this->assembleFile($upload);
        }

        $progress = ($upload->received_chunks / $upload->total_chunks) * 100;

        return response()->json([
            'received_chunks' => $upload->received_chunks,
            'total_chunks' => $upload->total_chunks,
            'progress' => round($progress, 1),
            'status' => $upload->status,
        ]);
    }

    public function status(Request $request, ChunkedUpload $upload)
    {
        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        $progress = $upload->total_chunks > 0
            ? round(($upload->received_chunks / $upload->total_chunks) * 100, 1)
            : 0;

        return response()->json([
            'status' => $upload->status,
            'received_chunks' => $upload->received_chunks,
            'total_chunks' => $upload->total_chunks,
            'progress' => $progress,
            'final_path' => $upload->status === 'completed' ? $upload->final_path : null,
        ]);
    }

    protected function assembleFile(ChunkedUpload $upload)
    {
        $disk = Storage::disk('local');
        $tempDir = $upload->temp_dir;
        $finalDir = 'uploads/videos/'.date('Y/m/d');
        $finalPath = $finalDir.'/'.$upload->filename;

        $disk->makeDirectory($finalDir);

        $outPath = storage_path('app/'.$tempDir.'/_assembled');
        $out = fopen($outPath, 'wb');

        if (! $out) {
            $upload->update(['status' => 'failed']);

            return;
        }

        for ($i = 0; $i < $upload->total_chunks; $i++) {
            $chunkFile = storage_path('app/'.$tempDir.'/chunk_'.str_pad($i, 6, '0', STR_PAD_LEFT));
            if (! file_exists($chunkFile)) {
                $upload->update(['status' => 'failed']);
                fclose($out);

                return;
            }
            $chunk = fopen($chunkFile, 'rb');
            stream_copy_to_stream($chunk, $out);
            fclose($chunk);
            unlink($chunkFile);
        }

        fclose($out);

        $disk->put($finalPath, file_get_contents($outPath));
        unlink($outPath);

        $upload->update([
            'status' => 'completed',
            'final_path' => $finalPath,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:mp4,webm,mkv,avi,mov|max:2097152',
            'anime_slug' => 'required|string',
        ]);

        $path = $request->file('file')->store("anime/{$data['anime_slug']}/videos", 'public');

        return response()->json([
            'path' => $path,
            'url' => url('storage/'.$path),
        ]);
    }

    public function cancel(ChunkedUpload $upload)
    {
        if ($upload->user_id !== auth()->id()) {
            abort(403);
        }

        $disk = Storage::disk('local');
        if ($disk->exists($upload->temp_dir)) {
            $disk->deleteDirectory($upload->temp_dir);
        }

        $upload->update(['status' => 'cancelled']);

        return response()->json(['status' => 'cancelled']);
    }
}
