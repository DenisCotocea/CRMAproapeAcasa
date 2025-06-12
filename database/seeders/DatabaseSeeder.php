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
        // Get existing unique codes to avoid collisions
        $usedCodes = Property::whereNotNull('unique_code')->pluck('unique_code')->toArray();
        $usedCodes = array_flip($usedCodes); // Faster lookups

        // Get ALL properties without a unique code
        $properties = Property::whereNull('unique_code')->get();

        foreach ($properties as $property) {
            do {
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (isset($usedCodes[$code]));

            // Assign and save the unique code
            $property->unique_code = $code;
            $property->saveQuietly();

            // Mark this code as used
            $usedCodes[$code] = true;
        }

        dd('It worked ✅');
    }
}
