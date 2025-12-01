<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Impersonate;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'user_role',
        'is_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function trackingInterests(): BelongsToMany
    {
        return $this->belongsToMany(TrackingInterest::class, 'user_tracking_interests')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function isSuperadmin(): bool
    {
        return $this->user_role === 'superadmin';
    }

    public function isTenantAdmin(): bool
    {
        return $this->user_role === 'tenant_admin';
    }

    public function isUser(): bool
    {
        return $this->user_role === 'user';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperadmin() || $this->isTenantAdmin();
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperadmin() || $this->isTenantAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return !$this->isSuperadmin();
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function assignTrackingInterest(TrackingInterest $trackingInterest, User $assignedBy): bool
    {
        return UserTrackingInterest::assignToUser($this, $trackingInterest, $assignedBy);
    }

    public function unassignTrackingInterest(TrackingInterest $trackingInterest): bool
    {
        return UserTrackingInterest::unassignFromUser($this, $trackingInterest);
    }

    public function canBeAssignedTrackingInterest(TrackingInterest $trackingInterest, User $assignedBy): array
    {
        return UserTrackingInterest::canAssign($this, $trackingInterest, $assignedBy);
    }

    public function getUserType(): string
    {
        return match($this->user_role) {
            'superadmin' => 'Superadmin',
            'tenant_admin' => 'Tenant Admin',
            'user' => 'User',
            default => 'Unknown',
        };
    }
}