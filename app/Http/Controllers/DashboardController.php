<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with headline stats, the leaderboard and recent games.
     */
    public function __invoke(Request $request, StatsService $stats): Response
    {
        $user = $request->user();

        $recentGames = $user->games()
            ->with('players')
            ->latest('played_on')
            ->latest('id')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'summary' => $stats->summary($user),
            'leaderboard' => $stats->leaderboard($user),
            'recentGames' => GameResource::collection($recentGames)->resolve(),
        ]);
    }
}
