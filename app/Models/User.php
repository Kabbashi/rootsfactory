<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'sso_subject'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Workspace is open to every authenticated team member. */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['editor', 'admin'], true);
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The identity the AI co-thinker posts under. Password-less, so it can
     * never be logged into; it only authors comments in discussions.
     */
    public static function aiAuthor(): self
    {
        return static::firstOrCreate(
            ['email' => config('ai.author.email')],
            ['name' => config('ai.author.name'), 'role' => 'system', 'password' => null],
        );
    }
}
