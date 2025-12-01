<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'is_enabled',
        'is_system',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function trackingInterests(): BelongsToMany
    {
        return $this->belongsToMany(TrackingInterest::class, 'tenant_tracking_interests')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function assignTrackingInterest(TrackingInterest $trackingInterest, User $assignedBy): bool
    {
        return TenantTrackingInterest::assignToTenant($this, $trackingInterest, $assignedBy);
    }

    public function unassignTrackingInterest(TrackingInterest $trackingInterest): bool
    {
        return TenantTrackingInterest::unassignFromTenant($this, $trackingInterest);
    }

    public function hasTrackingInterest(TrackingInterest $trackingInterest): bool
    {
        return $this->trackingInterests()->where('tracking_interest_id', $trackingInterest->id)->exists();
    }
}