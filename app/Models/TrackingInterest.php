<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrackingInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'interest',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_tracking_interests')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tracking_interests')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function pagesFound()
    {
        return $this->hasMany(PageFound::class);
    }

    public function shops()
    {
        return $this->hasManyThrough(
            Shop::class,
            PageFound::class,
            'tracking_interest_id',
            'id',
            'id',
            'page_id'
        )->distinct();
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'pages_found', 'tracking_interest_id', 'page_id')
            ->withPivot(['search_query_string_id', 'search_platform', 'serp_position'])
            ->withTimestamps();
    }
}