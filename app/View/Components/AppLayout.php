<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class AppLayout extends Component
{
    public array $settings;
    public $user;

    public function __construct()
    {
        // ✅ cache global settings
        $this->settings = Cache::remember('app_settings', 1800, function () {
            return Setting::query()
                ->pluck('value', 'key')
                ->toArray();
        });

        // ✅ current authenticated user
        $this->user = auth()->user();
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
