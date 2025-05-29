<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use App\Models\PublicScrapedProperty;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;


class PublicScraperService {

    protected $client;

    public function __construct() {
        $this->client = HttpClient::create([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            ]
        ]);
    }

    protected function fetchWithRetry($url, $retries = 3) {
        for ($i = 0; $i < $retries; $i++) {
            try {
                $response = $this->client->request('GET', $url);
                return $response->getContent();
            } catch (ClientExceptionInterface $e) {
                if (method_exists($e, 'getCode') && $e->getCode() === 429) {
                    $wait = pow(2, $i + 1); // exponential backoff
                    Log::channel('daily')->warning("Rate limited (429). Retrying in $wait seconds...");
                    sleep($wait);
                } else {
                    throw $e;
                }
            } catch (TransportException $e) {
                Log::channel('daily')->error("Transport error for $url: {$e->getMessage()}");
                sleep(2);
            }
        }

        throw new \Exception("Failed to fetch $url after {$retries} attempts.");
    }

    public function scrapeProperties($url) {
        try {
            Log::channel('daily')->info("Starting scraping from URL: {$url}");
            $currentPage = 1;
            $baseUrl = rtrim($url, '/');

            do {
                $currentUrl = $currentPage === 1 ? $baseUrl : "$baseUrl/?page=$currentPage";
                Log::channel('daily')->info("Scraping page: $currentUrl");

                // Determine property type
                $transaction = '';
                $type = '';
                if (str_contains($baseUrl, 'case')) $type = 'House';
                elseif (str_contains($baseUrl, 'terenuri')) $type = 'Land';
                elseif (str_contains($baseUrl, 'apartamente')) $type = 'Apartament';
                if (str_contains($baseUrl, 'garsoniera')) $type = 'Garsoniera';

                if (str_contains($baseUrl, 'de-inchiriat')) $transaction = 'Rent';
                elseif (str_contains($baseUrl, 'de-vanzare')) $transaction = 'Sale';

                // Get main page content
                $htmlContent = $this->fetchWithRetry($currentUrl);
                $crawler = new Crawler($htmlContent);
                $properties = $crawler->filter('div.article-item');

                if ($properties->count() === 0) {
                    Log::channel('daily')->info("No properties found on page $currentPage.");
                    $currentPage++;
                    continue;
                }

                $properties->each(function ($node) use ($transaction, $type) {
                    $propertyUrl = $node->filter('.article-title a')->attr('href');

                    if (PublicScrapedProperty::where('public_url', $propertyUrl)->exists()) {
                        Log::channel('daily')->info("Skipped duplicate property with url: {$propertyUrl}");
                        return;
                    }

                    // Add delay between property page visits
                    usleep(random_int(600000, 2000000)); // 0.6 to 2 sec

                    $propertyHtml = $this->fetchWithRetry($propertyUrl);
                    $propertyPage = new Crawler($propertyHtml);

                    $name = $this->extractName($propertyPage);
                    $price = $this->extractPrice($propertyPage);
                    $description = $this->extractDescription($propertyPage);
                    $images = $this->extractGalleryImages($propertyPage);
                    $attributes = $this->extractAttributes($propertyPage);
                    $location = $this->extractLocation($propertyPage);

                    PublicScrapedProperty::create([
                        'public_url' => $propertyUrl,
                        'title' => $name,
                        'transaction' => $transaction,
                        'type' => $type,
                        'county' => $location['county'],
                        'city' => $location['city'],
                        'address' => $location['city'] . ' ' . $location['area'],
                        'price' => $price,
                        'description' => $description,
                        'images' => json_encode($images),
                        'attributes' => json_encode($attributes),
                    ]);

                    Log::channel('daily')->info("Successfully scraped property: {$propertyUrl}");
                });

                sleep(random_int(1, 3));

                $currentPage++;

            } while ($crawler->filter('ul.pagination li.arrow a')->count() > 0);

            Log::channel('daily')->info("Scraping completed.");
        } catch (\Exception $e) {
            Log::channel('daily')->error("Scraping failed: {$e->getMessage()}");
        }
    }

    // Helper methods for extraction
    private function extractName($node) { return $node->filter('h1[itemprop="name"]')->text(); }

    private function extractPrice($node) {
        try {
            $priceAttr = $node->filter('span[itemprop="price"]')->attr('content');

            if ($priceAttr) {
                $price = preg_replace('/[^0-9]/', '', $priceAttr);
                return (int) $price;
            }

            $priceText = $node->filter('span[itemprop="price"]')->text();
            $priceText = preg_replace('/[^0-9]/', '', $priceText);

            return (int) $priceText;
        } catch (\Exception $e) {
            Log::error('Price extraction failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function extractLocation(Crawler $node): array
    {
        $location = [
            'county' => null,
            'city' => null,
            'area' => null,
        ];

        try {
            $locationLinks = $node->filter('.detail-info [itemprop="name"] a');

            if ($locationLinks->count() >= 1) {
                $location['county'] = trim($locationLinks->eq(0)->text());
            }

            if ($locationLinks->count() >= 2) {
                $location['city'] = trim($locationLinks->eq(1)->text());
            }

            if ($locationLinks->count() >= 3) {
                $areaText = trim($locationLinks->eq(2)->text());
                $areaText = preg_replace('/\s*Vezi pe hartă.*/i', '', $areaText);

                $location['area'] = trim($areaText);
            }

        } catch (\Exception $e) {
            Log::error('Location extraction failed: ' . $e->getMessage());
        }

        return $location;
    }

    private function extractDescription($node) {
        try {
            $html = $node->filter('div.article-description')->html();

            $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

            $text = strip_tags($html);

            return trim($text);
        } catch (\Exception $e) {
            Log::error('Description extraction failed: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    private function extractGalleryImages(Crawler $node): array
    {
        $images = [];

        try {
            $mainImg = $node->filter('#gallery .imgZone img')->first();
            if ($mainImg->count()) {
                $mainSrc = $mainImg->attr('src');
                if ($mainSrc) {
                    $images[] = $mainSrc;
                }
            }

            $node->filter('#detail-gallery ul.thumbZone img')->each(function (Crawler $imgNode) use (&$images) {
                $src = $imgNode->attr('src');
                if ($src) {
                    $images[] = $src;
                }
            });

            $images = array_unique($images);
        } catch (\Exception $e) {
            Log::error('Image gallery extraction failed: ' . $e->getMessage());
        }

        return $images;
    }

    private function extractAttributes($node) {
        $attributes = [];

        try {
            $node->filter('div.article-attributes .attribute-item')->each(function ($attrNode) use (&$attributes) {
                $label = trim($attrNode->filter('.attribute-label')->text());
                $value = trim($attrNode->filter('.attribute-value')->text());

                $attributes[$label] = $value;
            });
        } catch (\Exception $e) {
            Log::error('Attribute extraction failed: ' . $e->getMessage());
        }

        return $attributes;
    }
}

