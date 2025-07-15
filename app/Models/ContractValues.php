<?php

namespace App\Models;

use App\Enums\Contracts\ContractType;
use Illuminate\Database\Eloquent\Model;

class ContractValues extends Model
{
    protected $fillable = [
        'contract_id',
        'contract_field_id',
        'value',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function field()
    {
        return $this->belongsTo(ContractFields::class, 'contract_field_id');
    }
}
