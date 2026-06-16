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
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $allowedKeys = [
            'site_name',
            'site_description',
            'footer_text',
        ];

        $validatedFiles = $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:1024',
        ]);

        try {
            DB::transaction(function () use ($request, $allowedKeys) {

                // ✅ Save text settings
                foreach ($request->except('_token', 'logo', 'favicon') as $key => $value) {
                    if (in_array($key, $allowedKeys)) {
                        Setting::updateOrCreate(
                            ['key' => $key],
                            ['value' => trim($value)]
                        );
                    }
                }

                // ✅ Handle logo upload
                if ($request->hasFile('logo')) {
                    $this->replaceFileSetting('logo', $request->file('logo'));
                }

                // ✅ Handle favicon upload
                if ($request->hasFile('favicon')) {
                    $this->replaceFileSetting('favicon', $request->file('favicon'));
                }
            });

            // ✅ Central cache clear
            Cache::forget('settings');

            return back()->with('success', 'Settings updated.');
        } catch (\Throwable $e) {
            Log::error('Settings update failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update settings.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function replaceFileSetting(string $key, $file): void
    {
        $existing = Setting::where('key', $key)->value('value');

        // ✅ delete old file
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        $path = $file->store('settings', 'public');

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $path]
        );
    }
}
