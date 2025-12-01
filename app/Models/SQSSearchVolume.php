<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SQSSearchVolume extends Model
{
    use HasFactory;

    protected $table = 'sqs_search_volume';

    protected $fillable = [
        'search_query_string_id',
        'volume',
        'from_date',
        'to_date',
        'data_source',
        'description',
    ];

    protected $casts = [
        'volume' => 'integer',
        'from_date' => 'datetime',
        'to_date' => 'datetime',
    ];

    /**
     * Relazione con la search query string
     */
    public function searchQueryString()
    {
        return $this->belongsTo(SearchQueryString::class);
    }
}
