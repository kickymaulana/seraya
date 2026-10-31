<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nik', 'name', 'email', 'password', 'role', 'avatar_url', 'active', 'tanggal_masuk', 'position_id'])]
#[Hidden(['password', 'remember_token', 'nik'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'tanggal_masuk' => 'date',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function factories(): BelongsToMany
    {
        return $this->belongsToMany(Factory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}
