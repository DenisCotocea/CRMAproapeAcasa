<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\StoriaScrapedProperty;
use Illuminate\Console\Command;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\StoriaScraperService;
use Illuminate\Support\Facades\Storage;

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
        $storia->scrapeProperties($this->ApartamentRentUrl);
        $storia->scrapeProperties($this->GarsonieraRentUrl);
        $storia->scrapeProperties($this->GarsonieraBuyUrl);
        $storia->scrapeProperties($this->HouseBuyUrl);
        $storia->scrapeProperties($this->LandBuyUrl);

        Log::channel('daily')->info("Starting property import...");
        $scrapedProperties = StoriaScrapedProperty::where('imported', 0)->get();
        foreach ($scrapedProperties as $scraped) {
            try {
                $attributes = json_decode($scraped->attributes, true);
                $property = Property::create([
                    'name' => $scraped->title,
                    'type' => $scraped->type,
                    'county' => $scraped->county,
                    'city' => $scraped->city,
                    'address' => $scraped->address,
                    'price' => $scraped->price,
                    'tranzaction'  => $scraped->transaction,
                    'description' => $scraped->description,
                    'from_scraper' => 'Storia',
                    'scraper_link' => $scraped->storia_url,
                    'room_numbers' => $attributes['roomsNumbers'] ?? null,
                    'floor' => $attributes['floor'] ?? null,
                    'total_floors' => $attributes['totalFloors'] ?? null,
                    'construction_year' => $attributes['year'] ?? null,
                    'usable_area' => $attributes['surfaceArea'] ?? null,
                ]);

                foreach (json_decode($scraped->images) as $imageUrl) {
                    try {
                        $imageContent = Http::get($imageUrl)->body();
                        $filename = 'images/' . uniqid() . '.jpg';
                        Storage::disk('public')->put($filename, $imageContent);

                        Image::create([
                            'entity_id' => $property->id,
                            'entity_type' => Property::class,
                            'path' => $filename,
                        ]);

                    } catch (\Exception $e) {
                        Log::error("Failed to save image from URL {$imageUrl}: {$e->getMessage()}");
                    }
                }

                Log::channel('daily')->info("Successfully imported property: {$scraped->id}");
                $scraped->imported = 1;
                $scraped->property_id = $property->id;
                $scraped->save();
            } catch (\Exception $e) {
                Log::channel('daily')->error("Failed to import property {$scraped->id}: {$e->getMessage()}");
            }
        }

        Log::channel('daily')->info("Property import completed.");
    }
}
