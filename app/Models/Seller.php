<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'found_on_domain_id',
        'name',
        'is_certified',
        'affiliated_with_seller_id',
    ];

    protected $casts = [
        'is_reported' => 'boolean',
        'is_certified' => 'boolean',
    ];

    /**
     * Get the web domain where the seller was found.
     */
    public function foundOnDomain()
    {
        return $this->belongsTo(WebDomain::class, 'found_on_domain_id');
    }

    /**
     * Get all the pages associated with this seller.
     */
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get the parent seller (if this seller is affiliated with another seller).
     */
    public function parentSeller()
    {
        return $this->belongsTo(Seller::class, 'affiliated_with_seller_id');
    }

    /**
     * Get the affiliated sellers (sellers that are affiliated with this seller).
     */
    public function affiliatedSellers()
    {
        return $this->hasMany(Seller::class, 'affiliated_with_seller_id');
    }

    /**
     * Report this seller
     */
    public function report(): void
    {
        $this->is_reported = true;
        $this->reported_at = now();

        $this->save();
    }
}