<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_players()
    {
        $this->get(route('players.index'))->assertRedirect(route('login'));
    }

    public function test_players_index_only_lists_the_users_own_players()
    {
        $user = User::factory()->create();
        Player::factory()->for($user)->create(['name' => 'Mine']);
        Player::factory()->create(['name' => 'Someone Else']);

        $this->actingAs($user)
            ->get(route('players.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('players/Index')
                ->has('players', 1)
                ->where('players.0.name', 'Mine'));
    }

    public function test_a_player_can_be_added()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('players.store'), ['name' => 'Alex'])
            ->assertRedirect();

        $this->assertDatabaseHas('players', ['user_id' => $user->id, 'name' => 'Alex']);
    }

    public function test_player_names_must_be_unique_per_user()
    {
        $user = User::factory()->create();
        Player::factory()->for($user)->create(['name' => 'Alex']);
        Player::factory()->create(['name' => 'Sam']);

        $this->actingAs($user)
            ->post(route('players.store'), ['name' => 'Alex'])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('players.store'), ['name' => 'Sam'])
            ->assertSessionHasNoErrors();
    }

    public function test_a_player_can_be_renamed()
    {
        $player = Player::factory()->create(['name' => 'Alex']);

        $this->actingAs($player->user)
            ->patch(route('players.update', $player), ['name' => 'Alexis'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Alexis', $player->fresh()?->name);
    }

    public function test_users_cannot_view_or_modify_other_users_players()
    {
        $player = Player::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('players.show', $player))->assertForbidden();
        $this->actingAs($intruder)->patch(route('players.update', $player), ['name' => 'Hacked'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('players.destroy', $player))->assertForbidden();
    }

    public function test_a_player_without_games_can_be_deleted()
    {
        $player = Player::factory()->create();

        $this->actingAs($player->user)
            ->delete(route('players.destroy', $player))
            ->assertRedirect(route('players.index'));

        $this->assertModelMissing($player);
    }

    public function test_a_player_with_games_cannot_be_deleted()
    {
        $user = User::factory()->create();
        [$a, $b] = Player::factory()->for($user)->count(2)->create();
        $game = Game::factory()->for($user)->singles()->create();
        $game->players()->attach([$a->id => ['team' => 'a'], $b->id => ['team' => 'b']]);

        $this->actingAs($user)->delete(route('players.destroy', $a));

        $this->assertModelExists($a);
    }
}
