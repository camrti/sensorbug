<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'country',
    ];

    /**
     * Get all shops associated with this web domain.
     */
    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_domain');
    }

    /**
     * Get all sellers found on this web domain.
     */
    public function sellers()
    {
        return $this->hasMany(Seller::class, 'found_on_domain_id');
    }
}
