<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_type',
        'company_name',
        'email',
        'phone_number',
        'identification_number',
        'address',
        'notes',
    ];

    protected $casts = [
        'is_reported' => 'boolean',
    ];

    /**
     * Get the web domains associated with the shop.
     */
    public function webDomains()
    {
        return $this->belongsToMany(WebDomain::class, 'shop_domain');
    }

    /**
     * Get all the pages for the shop.
     */
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get all the tracking interests associated with this shop through its pages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function trackingInterests()
    {
        // Shop -> Page -> PageFound -> TrackingInterest
        return $this->hasManyThrough(
            TrackingInterest::class,
            Page::class,
            'shop_id', // Chiave esterna su pages che fa riferimento a shops
            'id', // Chiave locale su tracking_interests
            'id', // Chiave locale su shops
            'id' // Chiave esterna su pages che fa riferimento a tracking_interests via pages_found
        )->distinct();
    }

    /**
     * Get all the pages found associated with this shop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pagesFound()
    {
        return $this->hasManyThrough(
            PageFound::class,
            Page::class,
            'shop_id', // Chiave esterna su pages che fa riferimento a shops
            'page_id', // Chiave esterna su pages_found che fa riferimento a pages
            'id', // Chiave locale su shops
            'id' // Chiave locale su pages
        );
    }


    public function report()
    {
        $this->is_reported = true;
        $this->reported_at = now();
        $this->save();
    }
}
