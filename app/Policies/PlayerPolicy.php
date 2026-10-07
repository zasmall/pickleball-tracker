<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    /**
     * Determine whether the user can view the player.
     */
    public function view(User $user, Player $player): bool
    {
        return $user->id === $player->user_id;
    }

    /**
     * Determine whether the user can update the player.
     */
    public function update(User $user, Player $player): bool
    {
        return $user->id === $player->user_id;
    }

    /**
     * Determine whether the user can delete the player.
     */
    public function delete(User $user, Player $player): bool
    {
        return $user->id === $player->user_id;
    }
}
