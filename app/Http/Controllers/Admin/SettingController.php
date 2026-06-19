<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index (Load Settings)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $settings = Cache::remember('settings', 300, function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            return view('admin.settings.index', compact('settings'));

        } catch (\Throwable $e) {
            Log::error('Settings load failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to load settings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Settings
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $allowedKeys = [
            'site_name',
            'site_description',
            'footer_text',
        ];

        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:1024',
        ]);

        $uploadedFiles = [];
        $filesToDeleteAfterCommit = [];

        try {
            DB::transaction(function () use (
                $request,
                $allowedKeys,
                &$uploadedFiles,
                &$filesToDeleteAfterCommit
            ) {

                /*
                |--------------------------------------------------------------------------
                | Save Text Settings
                |--------------------------------------------------------------------------
                */
                foreach ($request->except('_token', 'logo', 'favicon') as $key => $value) {

                    if (!in_array($key, $allowedKeys, true)) {
                        continue;
                    }

                    Setting::updateOrCreate(
                        ['key' => $key],
                        ['value' => trim((string) $value)]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Logo Upload
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile('logo')) {
                    $this->replaceFileSetting(
                        'logo',
                        $request->file('logo'),
                        $uploadedFiles,
                        $filesToDeleteAfterCommit
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Favicon Upload
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile('favicon')) {
                    $this->replaceFileSetting(
                        'favicon',
                        $request->file('favicon'),
                        $uploadedFiles,
                        $filesToDeleteAfterCommit
                    );
                }
            });

            // ✅ delete old files AFTER DB success
            $this->deleteUploadedFiles($filesToDeleteAfterCommit);

            // ✅ clear cache
            Cache::forget('settings');

            return back()->with('success', 'Settings updated successfully.');

        } catch (\Throwable $e) {
            // ❌ rollback uploaded new files if DB failed
            $this->deleteUploadedFiles($uploadedFiles);

            Log::error('Settings update failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update settings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Replace File Setting (Safe)
    |--------------------------------------------------------------------------
    */
    protected function replaceFileSetting(
        string $key,
        $file,
        array &$uploadedFiles,
        array &$filesToDeleteAfterCommit
    ): void {
        $existing = Setting::where('key', $key)->value('value');

        // ✅ upload new file first
        $path = $file->store('settings', 'public');

        $uploadedFiles[] = $path;

        // ✅ schedule old file delete AFTER commit
        if ($existing && Storage::disk('public')->exists($existing)) {
            $filesToDeleteAfterCommit[] = $existing;
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $path]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Files
    |--------------------------------------------------------------------------
    */
    protected function deleteUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}