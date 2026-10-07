<?php

namespace Database\Seeders;

use App\Enums\GameFormat;
use App\Models\Player;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Database\Seeder;

class DemoGamesSeeder extends Seeder
{
    /**
     * Seed a handful of players and a few weeks of games for the given user.
     */
    public function run(GameService $games, User $user): void
    {
        $players = collect(['Zach', 'Jordan', 'Riley', 'Morgan', 'Casey', 'Taylor'])
            ->map(fn (string $name) => $user->players()->create(['name' => $name]));

        foreach (range(30, 1) as $daysAgo) {
            $format = fake()->boolean(75) ? GameFormat::Doubles : GameFormat::Singles;
            $size = $format->playersPerTeam();
            $picked = $players->shuffle()->take($size * 2)->values();
            $loser = fake()->numberBetween(2, 9);
            $aWins = fake()->boolean();

            $games->save($user, [
                'format' => $format->value,
                'played_on' => now()->subDays($daysAgo)->toDateString(),
                'location' => fake()->randomElement(['Community Center', 'City Park', 'Rec Club']),
                'team_a' => $picked->take($size)->map(fn (Player $p) => $p->id)->all(),
                'team_b' => $picked->slice($size)->map(fn (Player $p) => $p->id)->values()->all(),
                'team_a_score' => $aWins ? 11 : $loser,
                'team_b_score' => $aWins ? $loser : 11,
            ]);
        }
    }
}
