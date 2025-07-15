<?php

namespace App\Models;

use App\Enums\Contracts\ContractType;
use Illuminate\Database\Eloquent\Model;

class ContractFields extends Model
{
    protected $fillable = [
        'name',
        'label',
        'type',
        'contract_type',
        'required',
    ];


    protected $casts = [
        'contract_type' => ContractType::class,
        'required' => 'boolean',
    ];
}
