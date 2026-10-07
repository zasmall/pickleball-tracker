<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerRequest;
use App\Http\Resources\GameResource;
use App\Models\Player;
use App\Services\StatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    /**
     * List the user's players with their records.
     */
    public function index(Request $request, StatsService $stats): Response
    {
        return Inertia::render('players/Index', [
            'players' => $stats->leaderboard($request->user()),
        ]);
    }

    /**
     * Add a new player.
     */
    public function store(PlayerRequest $request): RedirectResponse
    {
        $request->user()->players()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Player added.')]);

        return back();
    }

    /**
     * Show a player's stats and game history.
     */
    public function show(Player $player, StatsService $stats): Response
    {
        Gate::authorize('view', $player);

        $games = $player->games()
            ->with('players')
            ->latest('played_on')
            ->latest('id')
            ->get();

        return Inertia::render('players/Show', [
            'player' => ['id' => $player->id, 'name' => $player->name],
            'stats' => $stats->forPlayer($player),
            'games' => GameResource::collection($games)->resolve(),
        ]);
    }

    /**
     * Rename a player.
     */
    public function update(PlayerRequest $request, Player $player): RedirectResponse
    {
        $player->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Player renamed.')]);

        return back();
    }

    /**
     * Delete a player who has not played any games.
     */
    public function destroy(Player $player): RedirectResponse
    {
        Gate::authorize('delete', $player);

        if ($player->games()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Players with recorded games cannot be deleted.')]);

            return back();
        }

        $player->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Player deleted.')]);

        return to_route('players.index');
    }
}
