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


        $scrapedProperties = OlxScrapedProperty::all()->where('imported', 0);
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
                        // Fetch the image content from the URL
                        $imageContent = Http::get($imageUrl)->body();

                        // Generate a unique filename for the image
                        $filename = 'images/' . uniqid() . '.jpg';

                        // Save the image to public storage
                        Storage::disk('public')->put($filename, $imageContent);

                        // Create the image record in the database
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
        // If the text is empty, return null
        if (empty($constructionYearText)) {
            return null;
        }

        // Use regex to extract the first 4-digit year
        if (preg_match('/\b(\d{4})\b/', $constructionYearText, $matches)) {
            return (int) $matches[1];
        }

        // If no year found, return null
        return null;
    }

    private function extractUsableArea($usableAreaText) {
        // If the text is empty, return null
        if (empty($usableAreaText)) {
            return null;
        }

        // Use regex to extract numeric value (integer or decimal)
        if (preg_match('/[\d.]+/', $usableAreaText, $matches)) {
            return (float) $matches[0];
        }

        // If no numeric value found, return null
        return null;
    }

    private function extractFloor($floorText) {
        if (empty($floorText)) {
            return null;
        }

        // Normalize the text (trim and lowercase)
        $floorText = strtolower(trim($floorText));

        // Handle special cases
        $floorMapping = [
            'parter' => 0,
            'demisol' => -1,
            'subsol' => -2,
            '10 sau peste' => 10
        ];

        // If it's directly in the mapping, return the value
        if (array_key_exists($floorText, $floorMapping)) {
            return $floorMapping[$floorText];
        }

        // If it's numeric, return it as an integer
        if (is_numeric($floorText)) {
            return (int)$floorText;
        }

        // If it doesn't match, return null
        return null;
    }
}
