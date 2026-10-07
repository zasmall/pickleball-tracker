<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Game
 */
class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $teamPlayers = fn (string $team) => $this->team($team)
            ->map(fn (Player $player) => ['id' => $player->id, 'name' => $player->name])
            ->all();

        return [
            'id' => $this->id,
            'played_on' => $this->played_on->toDateString(),
            'format' => $this->format->value,
            'location' => $this->location,
            'notes' => $this->notes,
            'team_a' => $teamPlayers('a'),
            'team_b' => $teamPlayers('b'),
            'team_a_score' => $this->team_a_score,
            'team_b_score' => $this->team_b_score,
            'winner' => $this->winningTeam(),
        ];
    }
}
