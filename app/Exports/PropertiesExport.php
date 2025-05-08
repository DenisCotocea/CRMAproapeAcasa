<?php

namespace App\Exports;

use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;

class PropertiesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Property::all();
    }

    public function headings(): array
    {
        return [
            'Id',
            'User Id',
            'Promoted',
            'Type',
            'Category',
            'Tranzaction',
            'Room_numbers',
            'Level',
            'Floor',
            'Total Floors',
            'Surface',
            'Construction year',
            'County',
            'City',
            'Address',
            'Price',
            'Description',
            'Details',
            'Partitioning',
            'Comfort',
            'Furnished',
            'Heating',
            'Balcony',
            'Garage',
            'Elevator',
            'Parking',
            'Availability status',
            'Available from',
            'Name',
            'Usable Area',
            'Land Area',
            'Yard Area',
            'Balcony Area',
            'Interior Condition',
            'Created At',
            'Updated At',
        ];
    }
}
