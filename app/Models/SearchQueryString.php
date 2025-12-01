<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchQueryString extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_interest_id',
        'search_intent',
        'query_string',
        'language_code',
        'source',
    ];

    /**
     * Get the tracking interest associated with this search query string.
     */
    public function trackingInterest()
    {
        return $this->belongsTo(TrackingInterest::class);
    }

    /**
     * Get all the page found records that used this search query string.
     */
    public function pagesFound()
    {
        return $this->hasMany(PageFound::class);
    }

    /**
     * Get all the search volume records for this search query string.
     */
    public function searchVolumes()
    {
        return $this->hasMany(SQSSearchVolume::class);
    }

    /**
     * Get the latest search volume record for this search query string.
     */
    public function latestSearchVolume()
    {
        return $this->hasOne(SQSSearchVolume::class)->latestOfMany('to_date');
    }
}
