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
            $properties = Property::all();

            foreach ($properties as $property) {

                $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                $property->unique_code = $code;
                $property->save();
            }

            dd('it worked');
    }
}
