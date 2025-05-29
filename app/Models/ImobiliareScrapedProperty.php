<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImobiliareScrapedProperty extends Model
{
    use HasFactory;
    protected $fillable = [
        'imobiliare_id',
        'imobiliare_url',
        'title',
        'county',
        'city',
        'address',
        'price',
        'description',
        'images',
        'attributes',
        'type',
        'transaction'
    ];

    protected $casts = [
        'imported' => 'boolean',
    ];
}
