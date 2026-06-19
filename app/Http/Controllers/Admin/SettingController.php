<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 300;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            return view('admin.settings.index', compact('settings'));
        } catch (\Throwable $e) {

            $this->logError('Settings load failed', $e);

            return $this->redirectError('Failed to load settings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $allowedKeys = [
            'site_name',
            'site_description',
            'footer_text',
        ];

        $request->validate([
            'logo'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
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
                | TEXT SETTINGS
                |--------------------------------------------------------------------------
                */
                foreach ($request->except('_token', 'logo', 'favicon') as $key => $value) {

                    if (!in_array($key, $allowedKeys, true)) {
                        continue;
                    }

                    $cleanValue = trim((string) $value);

                    if ($cleanValue === '') {
                        continue;
                    }

                    Setting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $cleanValue]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | FILE SETTINGS
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

                if ($request->hasFile('favicon')) {
                    $this->replaceFileSetting(
                        'favicon',
                        $request->file('favicon'),
                        $uploadedFiles,
                        $filesToDeleteAfterCommit
                    );
                }
            });

            /*
            |--------------------------------------------------------------------------
            | CLEANUP AFTER COMMIT
            |--------------------------------------------------------------------------
            */
            $this->deleteUploadedFiles($filesToDeleteAfterCommit);

            Cache::forget(self::CACHE_KEY);

            return back()->with('success', 'Settings updated successfully.');
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK FILE UPLOADS
            |--------------------------------------------------------------------------
            */
            $this->deleteUploadedFiles($uploadedFiles);

            $this->logError('Settings update failed', $e);

            return $this->redirectError('Failed to update settings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REPLACE FILE SETTING
    |--------------------------------------------------------------------------
    */
    protected function replaceFileSetting(
        string $key,
        $file,
        array &$uploadedFiles,
        array &$filesToDeleteAfterCommit
    ): void {
        $existing = Setting::where('key', $key)->value('value');

        /*
        |--------------------------------------------------------------------------
        | Upload new file first
        |--------------------------------------------------------------------------
        */
        $path = $file->store('settings', 'public');

        $uploadedFiles[] = $path;

        /*
        |--------------------------------------------------------------------------
        | Schedule old file deletion (only local files)
        |--------------------------------------------------------------------------
        */
        if ($this->isLocalStoragePath($existing)) {
            if (Storage::disk('public')->exists($existing)) {
                $filesToDeleteAfterCommit[] = $existing;
            }
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $path]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILE HELPERS
    |--------------------------------------------------------------------------
    */
    protected function isLocalStoragePath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return !str_starts_with($path, 'http://') &&
            !str_starts_with($path, 'https://');
    }

    protected function deleteUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {

            if (
                $this->isLocalStoragePath($path) &&
                Storage::disk('public')->exists($path)
            ) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
