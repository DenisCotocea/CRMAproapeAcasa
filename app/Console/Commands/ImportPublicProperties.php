<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use App\Models\PublicScrapedProperty;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PublicScraperService;
use Illuminate\Support\Facades\Storage;

class ImportPublicProperties extends Command {
    protected $signature = 'import:public-properties';
    protected $description = 'Import public scraped properties into the main properties table';

    protected $ApartamentBuyUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/apartamente/brasov/?commercial=false';

    protected $ApartamentRentUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-inchiriat/apartamente/brasov/?commercial=false';

    protected $GarsonieraBuyUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/apartamente/garsoniera/brasov/?commercial=false';

    protected $GarsonieraRentUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-inchiriat/apartamente/garsoniera/brasov/?commercial=false';

    protected $HouseBuyUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/case/brasov/?commercial=false';

    protected $LandBuyUrl = 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/terenuri/brasov/?commercial=false';

    public function handle()
    {

        $public = new PublicScraperService();

        $public->scrapeProperties($this->ApartamentBuyUrl);
        $public->scrapeProperties($this->ApartamentRentUrl);
        $public->scrapeProperties($this->GarsonieraBuyUrl);
        $public->scrapeProperties($this->GarsonieraRentUrl);
        $public->scrapeProperties($this->HouseBuyUrl);
        $public->scrapeProperties($this->LandBuyUrl);

        Log::channel('daily')->info("Starting property import...");

        $scrapedProperties = PublicScrapedProperty::where('imported', 0)->get();;
        foreach ($scrapedProperties as $scraped) {
            try {
                if ($scraped->county === 'Brasov') {
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
                        'from_scraper' => 'Publi24',
                        'scraper_link' => $scraped->public_url,
                        'promoted' => 0,
                        'usable_area' => $this->extractUsableArea($attributes['Suprafata utila'] ?? null),
                        'floor' => $this->extractFloor($attributes['Etaj'] ?? null),
                        'partitioning' => $attributes['Compartimentare'] ?? null,
                        'room_numbers' => $this->extractRooms($attributes['Numar camere'] ?? null),
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
                } else {
                    $scraped->delete();
                }
            } catch (\Exception $e) {
                Log::channel('daily')->error("Failed to import property {$scraped->id}: {$e->getMessage()}");
            }
        }

        Log::channel('daily')->info("Property import completed.");
    }

    private function extractUsableArea($usableAreaText) {
        if (empty($usableAreaText)) {
            return null;
        }

        if (preg_match('/[\d.]+/', $usableAreaText, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }

    private function extractRooms($roomsText) {
        if (empty($roomsText)) {
            return null;
        }

        if (preg_match('/\d+/', $roomsText, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function extractFloor($floorText) {
        if (empty($floorText)) {
            return null;
        }

        $floorText = strtolower(trim($floorText));

        if (strpos($floorText, 'parter') !== false) {
            return 0;
        }

        if (preg_match('/\d+/', $floorText, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

}
