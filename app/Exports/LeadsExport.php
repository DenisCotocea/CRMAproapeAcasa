<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;

class LeadsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Lead::all();
    }

    public function headings(): array
    {
        return [
            'Id',
            'User Id',
            'Name',
            'Email',
            'Phone',
            'Has Company',
            'Company_name',
            'Company_Email',
            'CUI',
            'Company_address',
            'CNP',
            'Date of birth',
            'County',
            'City',
            'Priority',
            'Status',
            'Last Contact',
            'Notes',
            'Created At',
            'Updated At',
        ];
    }
}
