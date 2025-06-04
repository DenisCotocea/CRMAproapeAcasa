<?php

// app/Console/Commands/ImportOlxProperties.php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use App\Models\OlxScrapedProperty;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\OlxScraperService;
use Illuminate\Support\Facades\Storage;

class ImportOlxProperties extends Command {
    protected $signature = 'import:olx-properties';
    protected $description = 'Import scraped properties into the main properties table';

    protected $ApartamentRentUrl = 'https://www.olx.ro/imobiliare/apartamente-garsoniere-de-inchiriat/brasov/';
    protected $ApartamentBuyUrl = 'https://www.olx.ro/imobiliare/apartamente-garsoniere-de-vanzare/brasov/';

    protected $HouseBuyUrl = 'https://www.olx.ro/imobiliare/case-de-vanzare/brasov/';

    protected $landBuyUrl = 'https://www.olx.ro/imobiliare/terenuri/brasov/';


    public function handle() {

        $olx = new OlxScraperService();

        $olx->scrapeProperties($this->ApartamentBuyUrl);
        $olx->scrapeProperties($this->ApartamentRentUrl);
        $olx->scrapeProperties($this->HouseBuyUrl);
        $olx->scrapeProperties($this->landBuyUrl);

        Log::channel('daily')->info("Starting property import...");

        $scrapedProperties = OlxScrapedProperty::where('imported', 0)->get();
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
                    'from_scraper' => 'Olx',
                    'scraper_link' => $scraped->olx_url,
                    'promoted' => $scraped->promoted,
                    'construction_year' => $this->extractConstructionYear($attributes['An constructie'] ?? null),
                    'usable_area' => $this->extractUsableArea($attributes['Suprafata utila'] ?? null),
                    'floor' => $this->extractFloor($attributes['Etaj'] ?? null),
                    'partitioning' => $attributes['Compartimentare'] ?? null,
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

    private function extractConstructionYear($constructionYearText) {
        if (empty($constructionYearText)) {
            return null;
        }

        if (preg_match('/\b(\d{4})\b/', $constructionYearText, $matches)) {
            return (int) $matches[1];
        }

        return null;
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

    private function extractFloor($floorText) {
        if (empty($floorText)) {
            return null;
        }

        $floorText = strtolower(trim($floorText));

        $floorMapping = [
            'parter' => 0,
            'demisol' => -1,
            'subsol' => -2,
            '10 sau peste' => 10
        ];

        if (array_key_exists($floorText, $floorMapping)) {
            return $floorMapping[$floorText];
        }

        if (is_numeric($floorText)) {
            return (int)$floorText;
        }

        return null;
    }
}
