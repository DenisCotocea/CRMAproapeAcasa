<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\OlxScrapedProperty;
use Illuminate\Support\Facades\Log;

class OlxScraperService {
    protected $client;

    public function __construct() {
        $this->client = HttpClient::create();
    }

    public function scrapeProperties($url) {
        try {
            Log::channel('daily')->info("Starting scraping from URL: {$url}");
            $currentPage = 1;
            $baseUrl = rtrim($url, '/');

            do {
                $currentUrl = $currentPage === 1 ? $baseUrl : "$baseUrl/?page=$currentPage";
                Log::channel('daily')->info("Scraping page: $currentUrl");

                $transaction = '';
                $type = '';

                if (str_contains($baseUrl, 'case-de-vanzare')) {
                    $type = 'House';
                } elseif (str_contains($baseUrl, 'terenuri')) {
                    $type = 'Land';
                } else {
                    $type = 'Apartament/Garsoniera';
                }

                if (str_contains($baseUrl, 'de-inchiriat')) {
                    $transaction = 'Rent';
                } elseif (
                    str_contains($baseUrl, 'de-vanzare') ||
                    str_contains($baseUrl, 'case-de-vanzare') ||
                    str_contains($baseUrl, 'terenuri')
                ) {
                    $transaction = 'Sale';
                }

                $response = $this->client->request('GET', $currentUrl);
                $htmlContent = $response->getContent();

                $crawler = new Crawler($htmlContent);

                $properties = $crawler->filter('div[data-cy="l-card"]');

                if ($properties->count() === 0) {
                    Log::channel('daily')->info("No properties found on page $currentPage.");
                    $currentPage++;
                    continue;
                }

                $properties->each(function ($node) use ($transaction, $type) {
                    $propertyUrl = $node->filter('a')->attr('href');
                    if (!str_contains($propertyUrl, '/d/oferta/')) {
                        return;
                    }
                    $propertyUrl = 'https://www.olx.ro' . $propertyUrl;
                    $promoted = $node->filter('.css-14uf3he')->count() > 0 ? 1 : 0;
                    $response = $this->client->request('GET', $propertyUrl);
                    $propertyPage = new Crawler($response->getContent());

                    $olxIdText = $propertyPage->filter('span.css-w85dhy')->text();
                    preg_match('/ID:\s*(\d+)/', $olxIdText, $matches);
                    $olxId = isset($matches[1]) ? $matches[1] : null;

                    if (OlxScrapedProperty::where('olx_id', $olxId)->exists()) {
                        Log::channel('daily')->info("Skipped duplicate property with hash: {$olxId}");
                        return;
                    }

                    $name = $this->extractName($propertyPage);
                    $price = $this->extractPrice($propertyPage);
                    $description = $this->extractDescription($propertyPage);
                    $images = $this->extractImages($propertyPage);
                    $attributes = $this->extractAttributes($propertyPage);


                    if (isset($attributes['Firma'])) {
                        Log::channel('daily')->info("Skipped property (Firma): {$olxId}");
                        return;
                    }

                    $olxScrapedProperty = OlxScrapedProperty::create([
                        'olx_id' => $olxId,
                        'olx_url' => $propertyUrl,
                        'title' => $name,
                        'promoted' => $promoted,
                        'transaction' => $transaction,
                        'type' => $type,
                        'county' => 'Brasov',
                        'city' => 'Brasov',
                        'address' => 'Brasov, Brasov',
                        'price' => $price,
                        'description' => $description,
                        'images' => $images,
                        'attributes' => json_encode($attributes),
                    ]);

                    Log::channel('daily')->info("Successfully scraped property: {$olxId}");
                });

                $currentPage++;

            } while ($crawler->filter('a[data-testid="pagination-forward"]')->count() > 0);

            Log::channel('daily')->info("Scraping completed.");

        } catch (\Exception $e) {
            Log::channel('daily')->error("Scraping failed: {$e->getMessage()}");
        }
    }

    // Helper methods for extraction
    private function extractName($node) { return $node->filter('div[data-cy="offer_title"] h4')->text(); }

    private function extractPrice($node) {
        try {
            // Extract price text and clean it
            $priceText = $node->filter('div[data-testid="ad-price-container"] h3')->text();

            $priceText = preg_replace('/[^0-9,.]/', '', $priceText);

            $priceText = str_replace(['.', ','], ['', ''], $priceText);

            return (int) $priceText;
        } catch (\Exception $e) {
            Log::error('Price extraction failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function extractDescription($node) { return $node->filter('div[data-cy="ad_description"] .css-19duwlz')->text() ?? 'Unknown'; }

    private function extractImages($node) {
        $images = $node->filter('div.swiper-wrapper img')->each(function ($img) {
            return $img->attr('src');
        });

        $images = array_values(array_unique($images));


        return json_encode($images, JSON_UNESCAPED_SLASHES);
    }

    private function extractAttributes($node) {
        $attributes = [];

        // Iterate through all attribute elements
        $node->filter('div[data-testid="ad-parameters-container"] p.css-1los5bp')->each(function ($attrNode) use (&$attributes) {
            $text = $attrNode->text();

            // If the text contains a colon, split it into key-value
            if (str_contains($text, ':')) {
                list($key, $value) = explode(':', $text, 2);
                $attributes[trim($key)] = trim($value);
            }
            elseif (stripos($text, 'Firma') !== false) {
                $attributes['Firma'] = 'Yes';
            }
        });

        return $attributes;
    }
}

