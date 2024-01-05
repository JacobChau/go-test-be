<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordQueued;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'birthdate',
        'avatar',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function hasAnyRole($roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([UserRole::Admin]);
    }

    public function isTeacher(): bool
    {
        return $this->hasAnyRole([UserRole::Teacher]);
    }

    /**
     * Send the queued email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailQueued);
    }

    /**
     * Send the queued password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordQueued($token));
    }

    // ------------------ Relationships ------------------
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'user_groups')->withTimestamps();
    }

    public function assessmentAttempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    public function ownedAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'created_by');
    }

    public function ownedQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    // ------------------ Scopes ------------------
    public function scopeEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeName($query, string $name)
    {
        return $query->where('name', $name);
    }

    public function scopeVerified($query, bool $verified)
    {
        return $verified ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at');
    }

    public function scopeRememberToken($query, string $refreshToken)
    {
        return $query->where('remember_token', $refreshToken);
    }
}
