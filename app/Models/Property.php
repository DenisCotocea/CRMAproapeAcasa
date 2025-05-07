<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'promoted',
        'type',
        'category',
        'tranzaction',
        'room_numbers',
        'level',
        'floor',
        'total_floors',
        'surface',
        'usable_area',
        'land_area',
        'yard_area',
        'balcony_area',
        'construction_year',
        'county',
        'city',
        'address',
        'price',
        'description',
        'details',
        'partitioning',
        'comfort',
        'furnished',
        'heating',
        'balcony',
        'garage',
        'elevator',
        'parking',
        'availability_status',
        'available_from',
        'locked_by_user_id',
        'locked_at',
        'interior_condition',
    ];

    protected $casts = [
        'promoted' => 'boolean',
        'balcony' => 'boolean',
        'garage' => 'boolean',
        'elevator' => 'boolean',
        'parking' => 'boolean',
        'price' => 'decimal:2',
        'surface' => 'float',
        'usable_area' => 'decimal:2',
        'land_area' => 'decimal:2',
        'yard_area' => 'decimal:2',
        'balcony_area' => 'decimal:2',
        'available_from' => 'date',
        'locked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
