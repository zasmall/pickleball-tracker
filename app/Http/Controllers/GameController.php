<?php

namespace App\Http\Controllers;

use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    /**
     * List the user's games, newest first.
     */
    public function index(Request $request): Response
    {
        $games = $request->user()->games()
            ->with('players')
            ->latest('played_on')
            ->latest('id')
            ->paginate(20);

        return Inertia::render('games/Index', [
            'games' => GameResource::collection($games->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'total' => $games->total(),
                'prev_page_url' => $games->previousPageUrl(),
                'next_page_url' => $games->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show the form for logging a new game.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('games/Form', [
            'game' => null,
            'players' => $this->playerOptions($request->user()),
        ]);
    }

    /**
     * Log a new game.
     */
    public function store(GameRequest $request, GameService $games): RedirectResponse
    {
        /** @var array{format: string, played_on: string, location?: string|null, notes?: string|null, team_a: array<int, int>, team_b: array<int, int>, team_a_score: int, team_b_score: int} $data */
        $data = $request->validated();

        $games->save($request->user(), $data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Game logged.')]);

        return to_route('games.index');
    }

    /**
     * Show the form for editing a game.
     */
    public function edit(Request $request, Game $game): Response
    {
        Gate::authorize('update', $game);

        $game->load('players');

        return Inertia::render('games/Form', [
            'game' => (new GameResource($game))->resolve(),
            'players' => $this->playerOptions($request->user()),
        ]);
    }

    /**
     * Update a game.
     */
    public function update(GameRequest $request, Game $game, GameService $games): RedirectResponse
    {
        /** @var array{format: string, played_on: string, location?: string|null, notes?: string|null, team_a: array<int, int>, team_b: array<int, int>, team_a_score: int, team_b_score: int} $data */
        $data = $request->validated();

        $games->save($request->user(), $data, $game);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Game updated.')]);

        return to_route('games.index');
    }

    /**
     * Delete a game.
     */
    public function destroy(Game $game): RedirectResponse
    {
        Gate::authorize('delete', $game);

        $game->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Game deleted.')]);

        return to_route('games.index');
    }

    /**
     * The user's players as select options.
     *
     * @return list<array{id: int, name: string}>
     */
    private function playerOptions(User $user): array
    {
        $players = $user->players()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Player $player) => ['id' => $player->id, 'name' => $player->name]);

        return array_values($players->all());
    }
}
