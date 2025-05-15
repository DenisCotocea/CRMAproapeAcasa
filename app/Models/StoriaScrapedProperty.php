<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoriaScrapedProperty extends Model
{
    use HasFactory;
    protected $fillable = [
        'storia_id',
        'storia_url',
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
