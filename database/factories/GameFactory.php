<?php

namespace Database\Factories;

use App\Enums\GameFormat;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loserScore = fake()->numberBetween(0, 9);
        $aWins = fake()->boolean();

        return [
            'user_id' => User::factory(),
            'played_on' => fake()->dateTimeBetween('-3 months'),
            'format' => GameFormat::Doubles,
            'location' => fake()->optional()->randomElement(['Community Center', 'City Park', 'Rec Club']),
            'team_a_score' => $aWins ? 11 : $loserScore,
            'team_b_score' => $aWins ? $loserScore : 11,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the game is a singles game.
     */
    public function singles(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => GameFormat::Singles,
        ]);
    }
}
