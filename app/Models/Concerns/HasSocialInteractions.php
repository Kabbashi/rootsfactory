<?php

namespace App\Models\Concerns;

use App\Models\CollaborationOffer;
use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Emoji reactions and offers to collaborate, shared by ideas and concepts.
 */
trait HasSocialInteractions
{
    /** Reaction palette. */
    public const EMOJIS = ['👍', '👎', '🙂', '❓'];

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function collaborationOffers(): MorphMany
    {
        return $this->morphMany(CollaborationOffer::class, 'offerable');
    }

    public function reactionCount(string $emoji): int
    {
        return $this->reactions->where('emoji', $emoji)->count();
    }

    public function hasReactionFrom(int $userId, string $emoji): bool
    {
        return $this->reactions()
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->exists();
    }

    /** Add or remove the given user's reaction with this emoji. */
    public function toggleReaction(int $userId, string $emoji): void
    {
        $existing = $this->reactions()
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $this->reactions()->create(['user_id' => $userId, 'emoji' => $emoji]);
        }
    }
}
