<?php

namespace App\Services;

use App\Enums\GameFormat;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * @phpstan-type Record array{played: int, wins: int, losses: int, win_rate: float, points_for: int, points_against: int, point_diff: int}
 * @phpstan-type LeaderboardRow array{id: int, name: string, played: int, wins: int, losses: int, win_rate: float, points_for: int, points_against: int, point_diff: int, streak: string|null}
 * @phpstan-type HeadToHead array{id: int, name: string, played: int, wins: int, losses: int, win_rate: float}
 */
class StatsService
{
    /**
     * Win/loss records for every one of the user's players, best first.
     *
     * @return list<LeaderboardRow>
     */
    public function leaderboard(User $user): array
    {
        $games = $this->gamesFor($user);

        $rows = $user->players()->orderBy('name')->get()
            ->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->name,
                ...$this->record($games, $player->id),
                'streak' => $this->streak($games, $player->id),
            ])
            ->all();

        usort($rows, fn (array $a, array $b) => [$b['played'] > 0, $b['win_rate'], $b['wins'], $b['point_diff']]
            <=> [$a['played'] > 0, $a['win_rate'], $a['wins'], $a['point_diff']]);

        return $rows;
    }

    /**
     * Detailed stats for a single player.
     *
     * @return array{overall: Record, singles: Record, doubles: Record, streak: string|null, partners: list<HeadToHead>, opponents: list<HeadToHead>}
     */
    public function forPlayer(Player $player): array
    {
        $games = $this->gamesFor($player->user)
            ->filter(fn (Game $game) => $this->teamOf($game, $player->id) !== null);

        $headToHead = ['partners' => [], 'opponents' => []];

        foreach ($games as $game) {
            $team = (string) $this->teamOf($game, $player->id);
            $won = $game->winningTeam() === $team;

            foreach ($game->players as $other) {
                if ($other->id === $player->id) {
                    continue;
                }

                $bucket = $this->teamOf($game, $other->id) === $team ? 'partners' : 'opponents';
                $headToHead[$bucket][$other->id] ??= ['id' => $other->id, 'name' => $other->name, 'played' => 0, 'wins' => 0, 'losses' => 0, 'win_rate' => 0.0];
                $headToHead[$bucket][$other->id]['played']++;
                $headToHead[$bucket][$other->id][$won ? 'wins' : 'losses']++;
            }
        }

        return [
            'overall' => $this->record($games, $player->id),
            'singles' => $this->record($games->filter(fn (Game $g) => $g->format === GameFormat::Singles), $player->id),
            'doubles' => $this->record($games->filter(fn (Game $g) => $g->format === GameFormat::Doubles), $player->id),
            'streak' => $this->streak($games, $player->id),
            'partners' => $this->rankHeadToHead($headToHead['partners']),
            'opponents' => $this->rankHeadToHead($headToHead['opponents']),
        ];
    }

    /**
     * Headline numbers for the dashboard.
     *
     * @return array{games: int, players: int, last_played_on: string|null}
     */
    public function summary(User $user): array
    {
        $lastPlayed = $user->games()->latest('played_on')->first();

        return [
            'games' => $user->games()->count(),
            'players' => $user->players()->count(),
            'last_played_on' => $lastPlayed?->played_on->toDateString(),
        ];
    }

    /**
     * All of the user's games with players loaded, oldest first.
     *
     * @return Collection<int, Game>
     */
    private function gamesFor(User $user): Collection
    {
        return $user->games()->with('players')->orderBy('played_on')->orderBy('id')->get();
    }

    /**
     * The team ('a' or 'b') a player was on in a game, or null if they did not play.
     */
    private function teamOf(Game $game, int $playerId): ?string
    {
        $player = $game->players->firstWhere('id', $playerId);

        return $player?->getRelationValue('pivot')?->getAttribute('team');
    }

    /**
     * @param  Collection<int, Game>  $games
     * @return Record
     */
    private function record(Collection $games, int $playerId): array
    {
        $record = ['played' => 0, 'wins' => 0, 'losses' => 0, 'win_rate' => 0.0, 'points_for' => 0, 'points_against' => 0, 'point_diff' => 0];

        foreach ($games as $game) {
            $team = $this->teamOf($game, $playerId);

            if ($team === null) {
                continue;
            }

            [$for, $against] = $team === 'a'
                ? [$game->team_a_score, $game->team_b_score]
                : [$game->team_b_score, $game->team_a_score];

            $record['played']++;
            $record[$for > $against ? 'wins' : 'losses']++;
            $record['points_for'] += $for;
            $record['points_against'] += $against;
        }

        $record['point_diff'] = $record['points_for'] - $record['points_against'];
        $record['win_rate'] = $this->winRate($record['wins'], $record['played']);

        return $record;
    }

    /**
     * The player's current streak, e.g. "W3" or "L1".
     *
     * @param  Collection<int, Game>  $games
     */
    private function streak(Collection $games, int $playerId): ?string
    {
        $streak = null;
        $count = 0;

        foreach ($games->reverse() as $game) {
            $team = $this->teamOf($game, $playerId);

            if ($team === null) {
                continue;
            }

            $result = $game->winningTeam() === $team ? 'W' : 'L';

            if ($streak !== null && $result !== $streak) {
                break;
            }

            $streak = $result;
            $count++;
        }

        return $streak === null ? null : $streak.$count;
    }

    /**
     * @param  array<int, HeadToHead>  $rows
     * @return list<HeadToHead>
     */
    private function rankHeadToHead(array $rows): array
    {
        $rows = array_map(fn (array $row) => [...$row, 'win_rate' => $this->winRate($row['wins'], $row['played'])], array_values($rows));

        usort($rows, fn (array $a, array $b) => [$b['played'], $b['win_rate']] <=> [$a['played'], $a['win_rate']]);

        return $rows;
    }

    private function winRate(int $wins, int $played): float
    {
        return $played === 0 ? 0.0 : round($wins / $played * 100, 1);
    }
}
