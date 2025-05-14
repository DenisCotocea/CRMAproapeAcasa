<?php

// app/Console/Commands/ImportOlxProperties.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImportOlxProperties extends Command
{
    protected $signature = 'import:olx-properties {json_file=olx_properties.json}';
    protected $description = 'Import OLX properties and store them in the database';

    public function handle()
    {
        $this->info('Starting to import OLX properties...');

        // Load the JSON data from the file
        $jsonData = file_get_contents(storage_path('app/' . $this->argument('json_file')));
        $properties = json_decode($jsonData, true);

        if (empty($properties)) {
            $this->error('No properties found in the JSON file.');
            return;
        }

        // Loop through each property and save it
        foreach ($properties as $propertyData) {
            $price = $propertyData['price'];
            // If the price contains the Euro sign, remove it and use the price as is
            if (strpos($price, '€') !== false) {
                $price = str_replace(' €', '', $price); // Remove euro sign
            } else {
                // If the price is in Lei, divide by 5.1
                $price = str_replace(' Lei', '', $price); // Remove Lei text if present
                $price = (float)$price / 5.1;
            }

            // Create the property
            $property = Property::create([
                'name' => $propertyData['title'] ?? 'Unknown Title',
                'user_id' => 1,
                'price' => (int)preg_replace('/[^0-9]/', '', $propertyData['price'] ?? '0'),
                'from_scraper' => 'OLX',
                'scraper_link' => $propertyData['url'] ?? null,
                'type' => 'Apartament',
                'description' => $propertyData['description'] ?? 'No description available',
                'category' => 'Residential',
                'county' => $propertyData['location'] ?? 'Unknown County',
                'city' => $propertyData['location'] ?? 'Unknown City',
                'address' => $propertyData['location'] ?? 'Unknown Address',
                'usable_area' => (float)($propertyData['attributes']['Suprafata utila'] ?? 0),
                'construction_year' => (int)($propertyData['attributes']['An constructie'] ?? null),
                'room_numbers' => (int)preg_replace('/[^0-9.]/', '', $propertyData['attributes']['Camere'] ?? '0'),
                'floor' => $propertyData['attributes']['Floor'] ?? null,
                'partitioning' => $propertyData['attributes']['Compartimentare'] ?? 'Unknown',
            ]);

            // Download and store images
            foreach ($propertyData['images'] as $imageUrl) {
                $image = $this->storeImageFromUrl($imageUrl);

                if ($image) {
                    Image::create([
                        'entity_id' => $property->id,
                        'entity_type' => Property::class,
                        'path' => $image,
                    ]);
                }
            }

            $this->info("Property '{$property->title}' imported successfully.");
        }

        $this->info('OLX properties import completed!');
    }

    /**
     * Download and store an image from a URL.
     *
     * @param string $url
     * @return string|null
     */
    private function storeImageFromUrl($url)
    {
        try {
            $imageContent = file_get_contents($url);
            if (!$imageContent) {
                return null;
            }

            $path = 'images/' . basename($url);
            Storage::disk('public')->put($path, $imageContent);

            return $path;
        } catch (\Exception $e) {
            $this->error("Failed to download image from $url: " . $e->getMessage());
            return null;
        }
    }
}
