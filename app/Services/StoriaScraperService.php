<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\StoriaScrapedProperty;
use Illuminate\Support\Facades\Log;

class StoriaScraperService {
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

                if (str_contains($baseUrl, 'casa')) {
                    $type = 'House';
                } elseif (str_contains($baseUrl, 'teren')) {
                    $type = 'Land';
                } else {
                    $type = 'Apartament/Garsoniera';
                }

                if (str_contains($baseUrl, 'inchiriere')) {
                    $transaction = 'Rent';
                } elseif (
                    str_contains($baseUrl, 'vanzare') ||
                    str_contains($baseUrl, 'casa') ||
                    str_contains($baseUrl, 'teren')
                ) {
                    $transaction = 'Sale';
                }

                $response = $this->client->request('GET', $currentUrl);
                $htmlContent = $response->getContent();

                $crawler = new Crawler($htmlContent);

                $properties = $crawler->filter('article[data-cy="listing-item"]');

                if ($properties->count() === 0) {
                    Log::channel('daily')->info("No properties found on page $currentPage.");
                    $currentPage++;
                    continue;
                }

                $properties->each(function ($node) use ($transaction, $type) {
                    $propertyUrl = $node->filter('a[data-cy="listing-item-link"]')->attr('href');
                    $propertyUrl = 'https://www.storia.ro' . $propertyUrl;

                    $response = $this->client->request('GET', $propertyUrl);
                    $propertyPage = new Crawler($response->getContent());

                    $storiaIdText = $propertyPage->filter('div[data-sentry-element="DetailsContainer"] p[data-sentry-element="DetailsProperty"]')->text();
                    preg_match('/ID\s*:\s*(\d+)/', $storiaIdText, $matches);
                    $storiaId = isset($matches[1]) ? $matches[1] : null;

                    if (StoriaScrapedProperty::where('storia_id', $storiaId)->exists()) {
                        Log::channel('daily')->info("Skipped duplicate property with hash: {$storiaId}");
                        return;
                    }

                    $name = $this->extractName($propertyPage);
                    $price = $this->extractPrice($propertyPage);
                    $description = $this->extractDescription($propertyPage);
                    $images = $this->extractImages($propertyPage);
                    $attributes = $this->extractAttributes($propertyPage);
                    $address = $this->extractAddress($propertyPage);
                    $addressParts = $this->extractAndSplitAddress($propertyPage);
                    if (isset($attributes['Tip vânzător:']) && strtolower($attributes['Tip vânzător:']) === 'agenție') {
                        Log::channel('daily')->info("Skipped property (Tip vânzător: agenție): {$storiaId}");
                        return;
                    }

                    $StoriaScrapedProperty = StoriaScrapedProperty::create([
                        'storia_id' => $storiaId,
                        'storia_url' => $propertyUrl,
                        'title' => $name,
                        'transaction' => $transaction,
                        'type' => $type,
                        'county' => $addressParts[1],
                        'city' => $addressParts[2],
                        'address' => $address,
                        'price' => $price,
                        'description' => $description,
                        'images' => json_encode($images),
                        'attributes' => json_encode($attributes),
                    ]);

                    Log::channel('daily')->info("Successfully scraped property: {$storiaId}");
                });

                $currentPage++;

                $nextPageBtn = $crawler->filter('li[aria-label="Go to next Page"]');

                $hasNextPage = $nextPageBtn->count() > 0 && $nextPageBtn->attr('aria-disabled') === 'false';

            } while ($hasNextPage);

            Log::channel('daily')->info("Scraping completed.");

        } catch (\Exception $e) {
            Log::channel('daily')->error("Scraping failed: {$e->getMessage()}");
        }
    }

    // Helper methods for extraction
    private function extractName($node) {return $node->filter('h1[data-cy="adPageAdTitle"]')->text();}

    private function extractPrice($node) {
        $priceText = $node->filter('strong[data-cy="adPageHeaderPrice"]')->text();
        $priceText = str_replace([' ', '€', 'Lei', ','], ['', '', '', ''], $priceText);

        return (int) $priceText;
    }

    private function extractAddress($node) {
        $filtered = $node->filter('div.css-pla15i');

        if ($filtered->count() === 0) {
            return 'Brasov';
        }

        $divText = $filtered->text();

        if (preg_match('/[A-ZĂÂȘȚÎ][^{}]*$/u', $divText, $matches)) {
            return trim($matches[0]);
        }

        return trim($divText);
    }

    private function extractAndSplitAddress($node) {
        // Filter the <a> tag with the attribute data-sentry-element="StyledLink"
        $aNode = $node->filter('a[data-sentry-element="StyledLink"]');

        if ($aNode->count() === 0) {
            return []; // no element found, return empty array
        }

        // Get the text inside <a>, ignoring the SVG (it won't be part of the text)
        $fullText = $aNode->text();

        // Split by commas
        $parts = explode(',', $fullText);

        // Trim whitespace around each part
        $parts = array_map('trim', $parts);

        return $parts;
    }


    private function extractDescription($node) {
        return $node->filter('div[data-cy="adPageAdDescription"]')->text();
    }

    private function extractImages($node) {
        $images = [];

        $pictures = $node->filter('picture');

        foreach ($pictures as $picture) {
            $pictureNode = new \Symfony\Component\DomCrawler\Crawler($picture);

            // Get all source srcset attributes inside picture
            $sourceSrcsets = $pictureNode->filter('source')->each(function ($source) {
                return $source->attr('srcset');
            });

            // Get img src attribute inside picture
            $imgSrcs = $pictureNode->filter('img')->each(function ($img) {
                return $img->attr('src');
            });

            // Add all found URLs to images array
            foreach ($sourceSrcsets as $srcset) {
                if ($srcset) {
                    $images[] = $srcset;
                }
            }

            foreach ($imgSrcs as $src) {
                if ($src) {
                    $images[] = $src;
                }
            }
        }

        // Remove duplicates and reindex
        return array_values(array_unique($images));
    }

    private function extractAttributes($node) {
        $attributes = [];

        // Selecting all attribute rows
        $node->filter('div[data-sentry-element="ItemGridContainer"]')->each(function ($attrNode) use (&$attributes) {
            $keyNode = $attrNode->filter('p.css-nlohq6')->first();
            $valueNode = $attrNode->filter('p.css-nlohq6, p.css-10ydh87')->last();

            if ($keyNode->count() > 0 && $valueNode->count() > 0) {
                $key = trim(str_replace(['<!-- -->'], '', $keyNode->text()));
                $value = trim(str_replace(['<!-- -->'], '', $valueNode->text()));
                $attributes[$key] = $value;
            }
        });

        return $attributes;
    }
}

