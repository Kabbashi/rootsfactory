<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'title', 'bio', 'email', 'password', 'role', 'sso_subject',
    'expertise', 'country_experience', 'languages', 'method_competencies', 'profile_public',
    'linkedin', 'instagram'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Global academic roles, mapped to their human label. */
    public const ROLES = [
        'researcher' => 'Researcher',
        'author' => 'Author',
        'reviewer' => 'Reviewer',
        'editor' => 'Editor',
        'admin' => 'Administrator',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expertise' => 'array',
            'country_experience' => 'array',
            'languages' => 'array',
            'method_competencies' => 'array',
            'profile_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (blank($user->slug) && filled($user->name)) {
                $base = Str::slug($user->name) ?: 'member';
                $slug = $base;
                $i = 2;

                while (static::where('slug', $slug)->whereKeyNot($user->getKey())->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $user->slug = $slug;
            }
        });
    }

    /** Public author profiles are slug-based. */
    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function isReviewer(): bool
    {
        return in_array($this->role, ['reviewer', 'editor', 'admin'], true);
    }

    /** Editorial staff: editors and admins. */
    public function isStaff(): bool
    {
        return $this->isEditor();
    }

    public function researchConcepts(): HasMany
    {
        return $this->hasMany(ResearchConcept::class);
    }

    /** Research projects this member belongs to. */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(ResearchProject::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Publications this member has authored. */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_author')
            ->withPivot('role', 'order')
            ->withTimestamps();
    }

    /** This author's publications that are public, newest first. */
    public function publishedPublications(): BelongsToMany
    {
        return $this->publications()->where('status', 'published')->orderByDesc('published_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    /**
     * A public profile requires explicit consent (profile_public) AND at least
     * one published publication. Without consent we never expose a member.
     */
    public function isPublicAuthor(): bool
    {
        return $this->profile_public
            && $this->publications()->where('status', 'published')->exists();
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
