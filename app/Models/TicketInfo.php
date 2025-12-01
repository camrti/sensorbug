<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketInfo extends Model
{
    use HasFactory;

    protected $table = 'ticket_info';

    protected $fillable = [
        'page_id',
        'price_at',
        'currency',
        'ticket_type',
        'selling_price',
        'description',
    ];

    protected $casts = [
        'price_at' => 'datetime',
        'selling_price' => 'decimal:4',
    ];

    /**
     * Relazione con la pagina
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
