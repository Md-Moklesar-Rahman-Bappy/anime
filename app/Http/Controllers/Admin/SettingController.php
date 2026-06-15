<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'site_name', 'site_description', 'footer_text',
        ];

        foreach ($request->except('_token') as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'favicon'], ['value' => $path]);
        }

        Cache::forget('setting_logo');
        Cache::forget('setting_favicon');
        Cache::forget('sitemap_urls');

        return back()->with('success', 'Settings updated.');
    }
}
