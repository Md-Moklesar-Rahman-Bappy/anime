<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public array $settings;

    public function __construct()
    {
        // ✅ same settings system as AppLayout
        $this->settings = Cache::remember(
            'guest_settings',
            1800,
            fn () => Setting::query()
                ->pluck('value', 'key')
                ->toArray()
        );
    }

    public function render(): View
    {
        return view('layouts.guest');
    }
}