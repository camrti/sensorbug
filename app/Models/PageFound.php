<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PageFound extends Model
{
    use HasFactory;

    protected $table = 'pages_found';

    protected $fillable = [
        'page_id',
        'tracking_interest_id',
        'search_query_string_id',
        'search_platform',
        'serp_position'
    ];

    /**
     * Get the page that was found.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the tracking interest this page was found for.
     */
    public function trackingInterest()
    {
        return $this->belongsTo(TrackingInterest::class);
    }

    /**
     * Get the search query string used to find this page, if any.
     */
    public function searchQueryString()
    {
        return $this->belongsTo(SearchQueryString::class);
    }
}
