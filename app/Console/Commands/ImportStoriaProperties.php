<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OlxScrapedProperty;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use App\Services\StoriaScraperService;

class ImportStoriaProperties extends Command {
    protected $signature = 'import:storia-properties';
    protected $description = 'Import storia scraped properties into the main properties table';

    protected $ApartamentBuyUrl = 'https://www.storia.ro/ro/rezultate/vanzare/apartament/brasov';

    protected $ApartamentRentUrl = 'https://www.storia.ro/ro/rezultate/inchiriere/apartament/brasov';

    protected $GarsonieraBuyUrl = 'https://www.storia.ro/ro/rezultate/vanzare/garsoniere/brasov';

    protected $GarsonieraRentUrl = 'https://www.storia.ro/ro/rezultate/inchiriere/garsoniere/brasov';

    protected $HouseBuyUrl = 'https://www.storia.ro/ro/rezultate/vanzare/casa/brasov';

    protected $LandBuyUrl = 'https://www.storia.ro/ro/rezultate/vanzare/teren/brasov';


    public function handle() {

        $storia = new StoriaScraperService();

        $storia->scrapeProperties($this->ApartamentBuyUrl);
        $storia->scrapeProperties($this->GarsonieraBuyUrl);
        $storia->scrapeProperties($this->GarsonieraRentUrl);
        $storia->scrapeProperties($this->GarsonieraBuyUrl);
        $storia->scrapeProperties($this->HouseBuyUrl);
        $storia->scrapeProperties($this->LandBuyUrl);


        Log::channel('daily')->info("Starting property import...");

        dd('da');

        foreach ($scrapedProperties as $scraped) {
            try {
//                $property = Property::create([
//                    'name' => $scraped->name,
//                    'type' => $scraped->type,
//                    'county' => $scraped->county,
//                    'city' => $scraped->city,
//                    'address' => $scraped->address,
//                    'price' => $scraped->price,
//                    'description' => $scraped->description,
//                ]);
//
//                Log::channel('daily')->info("Successfully imported property: {$scraped->id}");
//                $scraped->imported = 1;
//                $scraped->property_id = $property->id;
//                $scraped->save();
                $attributes = json_decode($scraped->attributes, true) ?? [];
                $allAttributes[] = $attributes;
            } catch (\Exception $e) {
                Log::channel('daily')->error("Failed to import property {$scraped->id}: {$e->getMessage()}");
            }
        }

        Log::channel('daily')->info("Property import completed.");
    }
}
