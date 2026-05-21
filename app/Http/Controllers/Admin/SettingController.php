<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
            'site_name', 'site_description', 'maintenance_mode',
            'default_theme', 'items_per_page', 'max_upload_size',
        ];

        foreach ($request->except('_token') as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        return back()->with('success', 'Settings updated.');
    }
}
