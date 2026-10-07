<?php

namespace Tests\Feature;

use App\Enums\GameFormat;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<int, Player>  $teamA
     * @param  array<int, Player>  $teamB
     */
    private function game(User $user, string $date, array $teamA, array $teamB, int $scoreA, int $scoreB): Game
    {
        $game = Game::factory()->for($user)->create([
            'played_on' => $date,
            'format' => count($teamA) === 1 ? GameFormat::Singles : GameFormat::Doubles,
            'team_a_score' => $scoreA,
            'team_b_score' => $scoreB,
        ]);

        $game->players()->attach(
            collect($teamA)->mapWithKeys(fn (Player $p) => [$p->id => ['team' => 'a']])->all()
            + collect($teamB)->mapWithKeys(fn (Player $p) => [$p->id => ['team' => 'b']])->all()
        );

        return $game;
    }

    public function test_player_stats_are_calculated_correctly()
    {
        $user = User::factory()->create();
        $me = Player::factory()->for($user)->create(['name' => 'Me']);
        $pat = Player::factory()->for($user)->create(['name' => 'Pat']);
        $sam = Player::factory()->for($user)->create(['name' => 'Sam']);
        $kim = Player::factory()->for($user)->create(['name' => 'Kim']);

        $this->game($user, '2026-09-01', [$me, $pat], [$sam, $kim], 11, 5); // W
        $this->game($user, '2026-09-02', [$me, $sam], [$pat, $kim], 8, 11); // L
        $this->game($user, '2026-09-03', [$me], [$kim], 11, 9);             // W
        $this->game($user, '2026-09-04', [$me, $pat], [$sam, $kim], 11, 3); // W

        $stats = app(StatsService::class)->forPlayer($me);

        $this->assertSame(4, $stats['overall']['played']);
        $this->assertSame(3, $stats['overall']['wins']);
        $this->assertSame(1, $stats['overall']['losses']);
        $this->assertSame(75.0, $stats['overall']['win_rate']);
        $this->assertSame(41, $stats['overall']['points_for']);
        $this->assertSame(28, $stats['overall']['points_against']);
        $this->assertSame(13, $stats['overall']['point_diff']);
        $this->assertSame('W2', $stats['streak']);

        $this->assertSame(1, $stats['singles']['played']);
        $this->assertSame(3, $stats['doubles']['played']);

        $this->assertSame('Pat', $stats['partners'][0]['name']);
        $this->assertSame(2, $stats['partners'][0]['wins']);
        $this->assertSame(['Kim', 'Sam', 'Pat'], array_column($stats['opponents'], 'name'));
        $this->assertSame(4, $stats['opponents'][0]['played']);
    }

    public function test_leaderboard_ranks_by_win_rate_and_puts_inactive_players_last()
    {
        $user = User::factory()->create();
        $alex = Player::factory()->for($user)->create(['name' => 'Alex']);
        $blair = Player::factory()->for($user)->create(['name' => 'Blair']);
        Player::factory()->for($user)->create(['name' => 'Casey']);

        $this->game($user, '2026-09-01', [$blair], [$alex], 11, 4);
        $this->game($user, '2026-09-02', [$blair], [$alex], 6, 11);
        $this->game($user, '2026-09-03', [$blair], [$alex], 11, 2);

        $rows = app(StatsService::class)->leaderboard($user);

        $this->assertSame(['Blair', 'Alex', 'Casey'], array_column($rows, 'name'));
        $this->assertSame(66.7, $rows[0]['win_rate']);
        $this->assertSame(0, $rows[2]['played']);
        $this->assertNull($rows[2]['streak']);
    }

    public function test_summary_reports_the_last_played_date()
    {
        $user = User::factory()->create();
        [$a, $b] = Player::factory()->for($user)->count(2)->create()->all();

        $this->game($user, '2026-09-01', [$a], [$b], 11, 4);
        $this->game($user, '2026-09-15', [$a], [$b], 11, 4);

        $summary = app(StatsService::class)->summary($user);

        $this->assertSame(['games' => 2, 'players' => 2, 'last_played_on' => '2026-09-15'], $summary);
    }
}
