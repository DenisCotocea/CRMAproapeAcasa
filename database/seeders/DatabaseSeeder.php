<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $usedCodes = Property::whereNotNull('unique_code')->pluck('unique_code')->toArray();
        $usedCodes = array_flip($usedCodes); // faster lookup

        Property::whereNull('unique_code')->chunk(100, function ($properties) use (&$usedCodes) {
            foreach ($properties as $property) {
                do {
                    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                } while (isset($usedCodes[$code]));

                $property->unique_code = $code;
                $property->save();

                $usedCodes[$code] = true;
            }
        });
    }
}
