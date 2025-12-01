<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTrackingInterest extends Model
{
    protected $fillable = [
        'user_id',
        'tracking_interest_id',
        'assigned_by_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trackingInterest(): BelongsTo
    {
        return $this->belongsTo(TrackingInterest::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public static function assignToUser(User $user, TrackingInterest $trackingInterest, User $assignedBy): bool
    {
        if ($assignedBy->isSuperadmin()) {
            return static::createOrUpdateAssignment($user, $trackingInterest, $assignedBy);
        }

        if ($assignedBy->isTenantAdmin()) {
            if (!$user->tenant_id || $user->tenant_id !== $assignedBy->tenant_id) {
                return false;
            }

            $tenantHasTrackingInterest = TenantTrackingInterest::where('tenant_id', $assignedBy->tenant_id)
                ->where('tracking_interest_id', $trackingInterest->id)
                ->exists();

            if (!$tenantHasTrackingInterest) {
                return false;
            }

            return static::createOrUpdateAssignment($user, $trackingInterest, $assignedBy);
        }

        return false;
    }

    public static function unassignFromUser(User $user, TrackingInterest $trackingInterest): bool
    {
        return static::where('user_id', $user->id)
            ->where('tracking_interest_id', $trackingInterest->id)
            ->delete() > 0;
    }

    private static function createOrUpdateAssignment(User $user, TrackingInterest $trackingInterest, User $assignedBy): bool
    {
        return static::updateOrCreate(
            [
                'user_id' => $user->id,
                'tracking_interest_id' => $trackingInterest->id,
            ],
            [
                'assigned_by_user_id' => $assignedBy->id,
            ]
        ) !== null;
    }

    public static function canAssign(User $user, TrackingInterest $trackingInterest, User $assignedBy): array
    {
        if ($assignedBy->isSuperadmin()) {
            return ['allowed' => true, 'reason' => 'Superadmin can assign any tracking interest'];
        }

        if ($assignedBy->isTenantAdmin()) {
            if (!$user->tenant_id || $user->tenant_id !== $assignedBy->tenant_id) {
                return ['allowed' => false, 'reason' => 'User does not belong to your tenant'];
            }

            $tenantHasTrackingInterest = TenantTrackingInterest::where('tenant_id', $assignedBy->tenant_id)
                ->where('tracking_interest_id', $trackingInterest->id)
                ->exists();

            if (!$tenantHasTrackingInterest) {
                return ['allowed' => false, 'reason' => 'This tracking interest is not assigned to your tenant'];
            }

            return ['allowed' => true, 'reason' => 'Tenant admin can assign tracking interests from their tenant'];
        }

        return ['allowed' => false, 'reason' => 'You do not have permission to assign tracking interests'];
    }
}