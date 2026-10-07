<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    /**
     * Determine whether the user can view the game.
     */
    public function view(User $user, Game $game): bool
    {
        return $user->id === $game->user_id;
    }

    /**
     * Determine whether the user can update the game.
     */
    public function update(User $user, Game $game): bool
    {
        return $user->id === $game->user_id;
    }

    /**
     * Determine whether the user can delete the game.
     */
    public function delete(User $user, Game $game): bool
    {
        return $user->id === $game->user_id;
    }
}
