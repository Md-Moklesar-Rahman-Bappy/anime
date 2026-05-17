<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\AnimeRequest;
use App\Models\Episode;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAnime = Anime::count();
        $totalEpisodes = Episode::count();
        $totalUsers = User::count();
        $totalReports = Report::where('status', 'pending')->count();
        $totalRequests = AnimeRequest::where('status', 'pending')->count();
        $recentAnime = Anime::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalAnime', 'totalEpisodes', 'totalUsers',
            'totalReports', 'totalRequests', 'recentAnime'
        ));
    }
}
