<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TenantTrackingInterest extends Model
{
    protected $fillable = [
        'tenant_id',
        'tracking_interest_id',
        'assigned_by_user_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function trackingInterest(): BelongsTo
    {
        return $this->belongsTo(TrackingInterest::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public static function assignToTenant(Tenant $tenant, TrackingInterest $trackingInterest, User $assignedBy): bool
    {
        if (!$assignedBy->isSuperadmin()) {
            return false;
        }

        return static::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'tracking_interest_id' => $trackingInterest->id,
            ],
            [
                'assigned_by_user_id' => $assignedBy->id,
            ]
        ) !== null;
    }

    public static function unassignFromTenant(Tenant $tenant, TrackingInterest $trackingInterest): bool
    {
        DB::beginTransaction();
        try {
            UserTrackingInterest::where('tracking_interest_id', $trackingInterest->id)
                ->whereIn('user_id', $tenant->users()->pluck('id'))
                ->delete();

            $deleted = static::where('tenant_id', $tenant->id)
                ->where('tracking_interest_id', $trackingInterest->id)
                ->delete();

            DB::commit();
            return $deleted > 0;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }
}