<?php

namespace Database\Seeders;

use App\Models\ContractFields;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddUniqueContractFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractFields::insert([
            ['name' => 'contract_serie', 'label' => 'Serie', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'name', 'label' => 'Nume complet', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'cnp', 'label' => 'CNP', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'id_series', 'label' => 'Serie buletin', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'id_type', 'label' => 'Act de identitate', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'id_number', 'label' => 'Numărul buletinului', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'company_address', 'label' => 'Sediu social', 'type' => 'text', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'phone', 'label' => 'Telefon', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'client_role', 'label' => 'Calitate de', 'type' => 'string', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'price', 'label' => 'Preț', 'type' => 'number', 'contract_type' => 'sale_unique', 'required' => true],
            ['name' => 'commission', 'label' => 'Comision datorat', 'type' => 'number', 'contract_type' => 'sale_unique', 'required' => true],
        ]);
    }
}
