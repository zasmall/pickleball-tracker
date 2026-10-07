<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @var Collection<int, Player> */
    private Collection $players;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->players = Player::factory()->for($this->user)->count(4)->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function doublesPayload(array $overrides = []): array
    {
        return [
            'format' => 'doubles',
            'played_on' => now()->toDateString(),
            'location' => 'City Park',
            'team_a' => [$this->players[0]->id, $this->players[1]->id],
            'team_b' => [$this->players[2]->id, $this->players[3]->id],
            'team_a_score' => 11,
            'team_b_score' => 7,
            ...$overrides,
        ];
    }

    public function test_a_doubles_game_can_be_logged()
    {
        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('games.index'));

        $game = Game::firstOrFail();

        $this->assertSame($this->user->id, $game->user_id);
        $this->assertSame('a', $game->winningTeam());
        $this->assertEqualsCanonicalizing([$this->players[0]->id, $this->players[1]->id], $game->team('a')->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$this->players[2]->id, $this->players[3]->id], $game->team('b')->pluck('id')->all());
    }

    public function test_a_singles_game_requires_one_player_per_team()
    {
        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload(['format' => 'singles']))
            ->assertSessionHasErrors(['team_a', 'team_b']);

        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload([
                'format' => 'singles',
                'team_a' => [$this->players[0]->id],
                'team_b' => [$this->players[1]->id],
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_games_cannot_end_in_a_tie()
    {
        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload(['team_a_score' => 10, 'team_b_score' => 10]))
            ->assertSessionHasErrors('team_b_score');
    }

    public function test_a_player_cannot_be_on_both_teams()
    {
        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload([
                'team_b' => [$this->players[0]->id, $this->players[3]->id],
            ]))
            ->assertSessionHasErrors('team_b');
    }

    public function test_games_cannot_be_in_the_future()
    {
        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload(['played_on' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors('played_on');
    }

    public function test_games_cannot_use_another_users_players()
    {
        $stranger = Player::factory()->create();

        $this->actingAs($this->user)
            ->post(route('games.store'), $this->doublesPayload([
                'team_b' => [$this->players[2]->id, $stranger->id],
            ]))
            ->assertSessionHasErrors('team_b.1');
    }

    public function test_a_game_can_be_updated_and_its_teams_resynced()
    {
        $this->actingAs($this->user)->post(route('games.store'), $this->doublesPayload());
        $game = Game::firstOrFail();

        $this->actingAs($this->user)
            ->put(route('games.update', $game), $this->doublesPayload([
                'format' => 'singles',
                'team_a' => [$this->players[3]->id],
                'team_b' => [$this->players[0]->id],
                'team_a_score' => 9,
                'team_b_score' => 11,
            ]))
            ->assertSessionHasNoErrors();

        $game->refresh()->load('players');

        $this->assertSame('b', $game->winningTeam());
        $this->assertCount(2, $game->players);
        $this->assertSame([$this->players[3]->id], $game->team('a')->pluck('id')->all());
    }

    public function test_a_game_can_be_deleted()
    {
        $this->actingAs($this->user)->post(route('games.store'), $this->doublesPayload());
        $game = Game::firstOrFail();

        $this->actingAs($this->user)
            ->delete(route('games.destroy', $game))
            ->assertRedirect(route('games.index'));

        $this->assertModelMissing($game);
        $this->assertDatabaseCount('game_player', 0);
    }

    public function test_users_cannot_edit_or_delete_other_users_games()
    {
        $this->actingAs($this->user)->post(route('games.store'), $this->doublesPayload());
        $game = Game::firstOrFail();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('games.edit', $game))->assertForbidden();
        $this->actingAs($intruder)->put(route('games.update', $game), $this->doublesPayload())->assertForbidden();
        $this->actingAs($intruder)->delete(route('games.destroy', $game))->assertForbidden();
    }

    public function test_games_index_lists_only_the_users_games()
    {
        $this->actingAs($this->user)->post(route('games.store'), $this->doublesPayload());
        Game::factory()->create();

        $this->actingAs($this->user)
            ->get(route('games.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/Index')
                ->has('games', 1)
                ->where('games.0.winner', 'a')
                ->where('pagination.total', 1));
    }
}
