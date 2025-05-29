<?php

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use App\Models\StoriaScrapedProperty;

class StoriaScraperService
{
    protected $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    public function scrapeProperties($url)
    {
        try {
            Log::channel('daily')->info("Starting scraping from URL: {$url}");
            $currentPage = 1;
            $baseUrl = rtrim($url, '/');

            do {
                $currentUrl = $currentPage === 1 ? $baseUrl : "$baseUrl/?page=$currentPage";

                Log::channel('daily')->info("Scraping page: $currentUrl");

                $response = $this->client->request('GET', $currentUrl);
                $htmlContent = $response->getContent();

                $crawler = new Crawler($htmlContent);
                $properties = $crawler->filter('article[data-cy="listing-item"]');

                if ($properties->count() === 0) {
                    Log::channel('daily')->info("No properties found on page $currentPage.");
                    $currentPage++;
                    continue;
                }

                $properties->each(function ($node) {
                    try {
                        $propertyUrl = $node->filter('a[data-cy="listing-item-link"]')->attr('href');
                        $propertyUrl = 'https://www.storia.ro' . $propertyUrl;

                        $response = $this->client->request('GET', $propertyUrl);
                        $propertyPage = new Crawler($response->getContent());
                        $data = $this->extractData($propertyPage);

                        $ad = $data['props']['pageProps']['ad'] ?? null;
                        if (!$ad) {
                            Log::channel('daily')->warning("Ad data missing, skipping.");
                            return;
                        }

                        if (!empty($ad['agency'])) {
                            Log::channel('daily')->info("Skipped agency-listed property with ID: " . ($ad['id'] ?? 'unknown'));
                            return;
                        }

                        $storiaId = $ad['id'] ?? null;
                        $name = $title = $ad['title'] ?? 'No Title';
                        $description = $ad['description'] ?? '';
                        $description = trim(preg_replace('/\s+/', ' ', preg_replace('/<[^>]+>/', ' ', $description)));
                        $characteristics = $ad['characteristics'] ?? [];

                        $price = $ad['target']['Price'] ?? null;
                        $surfaceArea = $ad['target']['Area'] ?? null;
                        $totalfloors = $ad['target']['Building_floors_num'] ?? null;
                        $constractionYear = $ad['target']['Build_year'] ?? null;
                        $offerType = $ad['target']['OfferType'] ?? '';
                        $propertyType = ucfirst($ad['target']['ProperType'] ?? 'Unknown');

                        $transaction = match ($offerType) {
                            'vanzare' => 'Sale',
                            'inchiriere' => 'Rent',
                            default => 'Unknown',
                        };

                        $county = 'Brasov';
                        $city = $ad['location']['address']['city']['name'] ?? null;
                        $street = $ad['location']['address']['street']['name'] ?? null;
                        $address = $county . " "  . $city . " " .$street;

                        $roomsNumber = null;
                        $floor = null;
                        foreach ($characteristics as $char) {
                            if ($char['key'] === 'rooms_num') {
                                $roomsNumber = $char['localizedValue'];
                            }
                            if ($char['key'] === 'floor_no') {
                                $floor = $char['localizedValue'];
                            }
                        }

                        $images = $ad['images'] ?? [];
                        $imageLinks = [];
                        foreach ($images as $img) {
                            if (isset($img['large'])) {
                                $imageLinks[] = $img['large'];
                            }
                        }

                        if (StoriaScrapedProperty::where('storia_id', $storiaId)->exists()) {
                            Log::channel('daily')->info("Skipped duplicate property with ID: {$storiaId}");
                            return;
                        }

                        $attributes = [
                            'roomsNumber' => $roomsNumber,
                            'floor' => $floor,
                            'surfaceArea' => $surfaceArea,
                            'totalFloors' => $totalfloors,
                            'year' => $constractionYear,
                        ];

                        StoriaScrapedProperty::create([
                            'storia_id' => $storiaId,
                            'storia_url' => $propertyUrl,
                            'title' => $name,
                            'transaction' => $transaction,
                            'type' => $propertyType,
                            'county' => $county,
                            'city' => $city,
                            'address' => $address,
                            'price' => $price ?? 0,
                            'description' => $description,
                            'images' => json_encode($imageLinks),
                            'attributes' => json_encode($attributes),
                        ]);

                        Log::channel('daily')->info("Successfully scraped property: {$storiaId}");

                    } catch (\Throwable $e) {
                        Log::channel('daily')->error("Failed to scrape a property: {$e->getMessage()}");
                    }
                });
                sleep(2);
                $currentPage++;
            } while ($currentPage < 110);

            Log::channel('daily')->info("Scraping completed.");

        } catch (\Throwable $e) {
            Log::channel('daily')->error("Scraping failed: {$e->getMessage()}");
        }
    }

    private function extractData($node)
    {
        $scriptNode = $node->filter('script#__NEXT_DATA__');

        if ($scriptNode->count() === 0) {
            throw new \Exception("JSON script not found");
        }

        $jsonContent = $scriptNode->text();
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decoding failed: " . json_last_error_msg());
        }

        return $data;
    }
}
