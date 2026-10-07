<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameService
{
    /**
     * Create or update a game along with the players on each team.
     *
     * @param  array{format: string, played_on: string, location?: string|null, notes?: string|null, team_a: array<int, int>, team_b: array<int, int>, team_a_score: int, team_b_score: int}  $data
     */
    public function save(User $user, array $data, ?Game $game = null): Game
    {
        return DB::transaction(function () use ($user, $data, $game) {
            $game ??= $user->games()->make();

            $game->fill([
                'format' => $data['format'],
                'played_on' => $data['played_on'],
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
                'team_a_score' => $data['team_a_score'],
                'team_b_score' => $data['team_b_score'],
            ])->save();

            $roster = [];

            foreach (['a', 'b'] as $team) {
                foreach ($data["team_{$team}"] as $playerId) {
                    $roster[(int) $playerId] = ['team' => $team];
                }
            }

            $game->players()->sync($roster);

            return $game;
        });
    }
}
