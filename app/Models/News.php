<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'for_user_id',
        'for_tenant_id',
        'for_tracking_interest_id',
        'added_by_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user this news is for.
     */
    public function forUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'for_user_id');
    }

    /**
     * Get the tenant this news is for.
     */
    public function forTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'for_tenant_id');
    }

    /**
     * Get the tracking interest this news is for.
     */
    public function forTrackingInterest(): BelongsTo
    {
        return $this->belongsTo(TrackingInterest::class, 'for_tracking_interest_id');
    }

    /**
     * Get the user who added this news.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}