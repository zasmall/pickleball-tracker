<?php

namespace App\Models;

use App\Enums\GameFormat;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $played_on
 * @property GameFormat $format
 * @property string|null $location
 * @property int $team_a_score
 * @property int $team_b_score
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['played_on', 'format', 'location', 'team_a_score', 'team_b_score', 'notes'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'played_on' => 'date',
            'format' => GameFormat::class,
            'team_a_score' => 'integer',
            'team_b_score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Player, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)->withPivot('team');
    }

    /**
     * The players on the given team ('a' or 'b').
     *
     * @return Collection<int, Player>
     */
    public function team(string $team): Collection
    {
        /** @var Collection<int, Player> $players */
        $players = $this->players;

        return $players
            ->filter(fn (Player $player) => $player->getRelationValue('pivot')?->getAttribute('team') === $team)
            ->values();
    }

    /**
     * The winning team ('a' or 'b'). Ties are not allowed.
     */
    public function winningTeam(): string
    {
        return $this->team_a_score > $this->team_b_score ? 'a' : 'b';
    }
}
