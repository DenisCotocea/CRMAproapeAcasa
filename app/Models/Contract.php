<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Contracts\ContractType;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_type',
        'agent_id',
        'leads',
        'agent_signed',
        'signature_agent',
        'agent_signed_at',
        'client_signed',
        'signature_client',
        'client_signed_at',
    ];

    protected $casts = [
        'contract_type' => ContractType::class,
        'signed' => 'boolean',
        'signed_at' => 'datetime',
        'agent_signed_at' => 'datetime',
        'client_signed_at' => 'datetime',
    ];

    public function agent(){
        return $this->belongsTo(User::class);
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class);
    }

    public function fields()
    {
        return $this->hasMany(ContractFields::class);
    }

    public function values()
    {
        return $this->hasMany(ContractValues::class);
    }
}
