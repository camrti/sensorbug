<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'whitelist_class',
        'currently_sells',
        'is_selling_page',
        'seller_id',
        'redirects_to_page_id',
        'page_url',
        'ticket_name',
        'notes'
    ];

    protected $casts = [
        'currently_sells' => 'boolean',
        'is_selling_page' => 'boolean',
        'is_reported' => 'boolean',
    ];

    /**
     * Get the shop that owns the page.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the seller associated with this page.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the page that this page redirects to.
     */
    public function redirectsToPage()
    {
        return $this->belongsTo(Page::class, 'redirects_to_page_id');
    }

    /**
     * Get the pages that redirect to this page.
     */
    public function redirectingPages()
    {
        return $this->hasMany(Page::class, 'redirects_to_page_id');
    }

    /**
     * Get all the tracking interests associated with this page through pages_found.
     */
    public function trackingInterests()
    {
        return $this->belongsToMany(TrackingInterest::class, 'pages_found')
                    ->withPivot(['search_query_string_id', 'search_platform', 'serp_position'])
                    ->withTimestamps();
    }

    /**
     * Get all the page found records for this page.
     */
    public function pagesFound()
    {
        return $this->hasMany(PageFound::class);
    }

    /**
     * Report this page
     */
    public function report(): void
    {
        $this->is_reported = true;
        $this->reported_at = now();

        $this->save();
    }
}