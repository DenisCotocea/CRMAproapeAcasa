<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PublicScrapedProperty extends Model
{
    use HasFactory;
    protected $fillable = [
        'public_url',
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
