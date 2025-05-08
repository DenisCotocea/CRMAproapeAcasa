<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'path',
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
