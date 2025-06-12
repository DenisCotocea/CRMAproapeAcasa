<?php

namespace App\Jobs\Publi24;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScrapePubli24PageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(
        protected string $baseUrl,
        protected int $page,
        protected string $category
    ) {}

    public function handle(): void
    {
        $url = $this->page === 1
            ? $this->baseUrl
            : $this->baseUrl . '&page=' . $this->page;

        $response = $this->getWithRetries($url);

        if (!$response) {
            Log::channel('publi24_scraper')->error("Failed to fetch Publi24 page {$this->page} at {$url}");
            $this->release($this->backoff);
            return;
        }

        $transaction = '';
        $type = '';
        if (str_contains($this->baseUrl, 'case')) $type = 'House';
        elseif (str_contains($this->baseUrl, 'terenuri')) $type = 'Land';
        elseif (str_contains($this->baseUrl, 'apartamente')) $type = 'Apartament';
        if (str_contains($this->baseUrl, 'garsoniera')) $type = 'Garsoniera';

        if (str_contains($this->baseUrl, 'de-inchiriat')) $transaction = 'Rent';
        elseif (str_contains($this->baseUrl, 'de-vanzare')) $transaction = 'Sale';

        $html = $response->body();
        $crawler = new Crawler($html);

        $ads = $crawler->filter('div.article-item');

        if ($ads->count() === 0) {
            Log::channel('publi24_scraper')->info("No ads found on page {$this->page} for {$url}");
            return;
        }

        $ads->each(function (Crawler $adNode) use ($transaction, $type){
            try {
                $propertyUrl = $adNode->filter('.article-title a')->attr('href');

                if (Property::where('scraper_link', $propertyUrl)->exists()) {
                    Log::channel('publi24_scraper')->info("Property already exists: {$propertyUrl}");
                    return;
                }

                $response = $this->getWithRetries($propertyUrl);

                $propertyPage = new Crawler($response->body());

                $name = $this->extractName($propertyPage);
                $price = $this->extractPrice($propertyPage);
                $description = $this->extractDescription($propertyPage);
                $images = $this->extractGalleryImages($propertyPage);
                $attributes = $this->extractAttributes($propertyPage);
                $location = $this->extractLocation($propertyPage);

                \App\Jobs\Publi24\ImportPropertyJob::dispatch([
                    'title'    => $name,
                    'url'      => $propertyUrl,
                    'price'    => $price,
                    'description'    => $description,
                    'images' => json_encode($images),
                    'attributes' => json_encode($attributes),
                    'county' => $location['county'],
                    'city' =>  $location['city'],
                    'address' =>  $location['city'] . ' ' . $location['area'],
                    'transaction' => $transaction,
                    'type' => $type,
                ]);
            } catch (\Throwable $e) {
                Log::channel('publi24_scraper')->error("Error scraping individual ad: {$e->getMessage()}");
            }
        });
    }

    protected function getWithRetries(string $url, int $maxRetries = 3)
    {
        $attempt = 0;
        do {
            try {
                sleep(rand(1, 3));

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                    'Referer' => 'https://www.google.com/',
                    'sec-ch-ua' => '"Chromium";v="125", "Not.A/Brand";v="8"',
                    'sec-ch-ua-mobile' => '?0',
                    'sec-ch-ua-platform' => '"Windows"',
                    'Sec-Fetch-Site' => 'none',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-User' => '?1',
                    'Sec-Fetch-Dest' => 'document',
                ])->timeout(15)->get($url);

                if ($response->status() === 403) {
                    Log::channel('publi24_scraper')->warning("Publi24 403 Forbidden: {$url} - Backing off longer");
                    sleep(rand(10, 30));
                    return null;
                }

                if ($response->successful()) {
                    return $response;
                }

                Log::channel('publi24_scraper')->warning("Failed to fetch {$url} with status {$response->status()}");

            } catch (\Throwable $e) {
                Log::channel('publi24_scraper')->error("HTTP error fetching {$url}: {$e->getMessage()}");
            }

            $attempt++;
        } while ($attempt < $maxRetries);

        Log::channel('publi24_scraper')->error("Max retries reached for {$url}");
        return null;
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
            Log::channel('publi24_scraper')->error('Price extraction failed: ' . $e->getMessage());
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
            Log::channel('publi24_scraper')->error('Location extraction failed: ' . $e->getMessage());
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
            Log::channel('publi24_scraper')->error('Description extraction failed: ' . $e->getMessage());
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
            Log::channel('publi24_scraper')->error('Image gallery extraction failed: ' . $e->getMessage());
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
            Log::channel('publi24_scraper')->error('Attribute extraction failed: ' . $e->getMessage());
        }

        return $attributes;
    }
}
