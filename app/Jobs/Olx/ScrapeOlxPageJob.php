<?php

namespace App\Jobs\Olx;

use App\Models\OlxScrapedProperty;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeOlxPageJob implements ShouldQueue
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
            : $this->baseUrl . '?page=' . $this->page;

        $response = $this->getWithRetries($url);
        if (!$response) {
            Log::channel('olx_scraper')->error("Failed to fetch OLX page {$this->page} at {$url}");
            $this->release($this->backoff);
            return;
        }

        $transaction = '';
        $type = '';

        if (str_contains($this->baseUrl, 'case-de-vanzare')) {
            $type = 'House';
        } elseif (str_contains($this->baseUrl, 'terenuri')) {
            $type = 'Land';
        } else {
            $type = 'Apartament/Garsoniera';
        }

        if (str_contains($this->baseUrl, 'de-inchiriat')) {
            $transaction = 'Rent';
        } elseif (
            str_contains($this->baseUrl, 'de-vanzare') ||
            str_contains($this->baseUrl, 'case-de-vanzare') ||
            str_contains($this->baseUrl, 'terenuri')
        ) {
            $transaction = 'Sale';
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $ads = $crawler->filter('div[data-cy="l-card"]');

        if ($ads->count() === 0) {
            Log::channel('olx_scraper')->info("No ads found on page {$this->page} for {$url}");
            return;
        }

        $ads->each(function (Crawler $adNode) use ($transaction, $type){
            try {
                if (!$adNode->filter('a')->count()) {
                    Log::channel('olx_scraper')->info("Ad node missing <a> tag");
                    return;
                }
                $propertyUrl = $adNode->filter('a')->attr('href');
                if (!str_contains($propertyUrl, '/d/oferta/')) {
                    Log::channel('olx_scraper')->info("Property is from a third party: " . $propertyUrl);
                    return;
                }
                $propertyUrl = 'https://www.olx.ro' . $propertyUrl;

                $response = $this->getWithRetries($propertyUrl);

                $propertyPage = new Crawler($response->body());

                $jsonData = $this->extractJsonData($propertyPage);

                if (!$jsonData) {
                    Log::channel('olx_scraper')->warning("Could not extract JSON data from: " . $propertyUrl);
                    return;
                }

                $olxId = $jsonData['sku'];

                if (Property::where('scraper_link', $propertyUrl)->orWhere('scraper_code', $olxId)->exists()) {
                    Log::channel('olx_scraper')->info("Property already exists: {$propertyUrl} or OLX ID: {$olxId}");
                    return;
                }

                $name = $jsonData['name'];
                $price = $jsonData['offers']['price'];
                $description = $jsonData['description'];
                $images = $jsonData['image'];
                $attributes = $this->extractAttributes($propertyPage);

                if (isset($attributes['Firma'])) {
                    Log::channel('olx_scraper')->info("Skipped property (Firma): {$propertyUrl}");
                    return;
                }

                ImportPropertyJob::dispatch([
                    'olx_id' => $olxId,
                    'title'    => $name,
                    'url'      => $propertyUrl,
                    'price'    => $price,
                    'description'    => $description,
                    'images'    => $images,
                    'attributes'    => json_encode($attributes),
                    'county' => 'Brasov',
                    'city' => 'Brasov',
                    'address' => 'Brasov, Brasov',
                    'transaction' => $transaction,
                    'type' => $type,
                ]);
            } catch (\Throwable $e) {
                Log::channel('olx_scraper')->error("Error scraping individual ad: {$e->getMessage()}");
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
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ])->timeout(15)->get($url);

                if ($response->status() === 403) {
                    Log::channel('olx_scraper')->warning("OLX 403 Forbidden: {$url} - Backing off longer");
                    sleep(rand(10, 30));
                    return null;
                }

                if ($response->successful()) {
                    return $response;
                }

                Log::channel('olx_scraper')->warning("Failed to fetch {$url} with status {$response->status()}");

            } catch (\Throwable $e) {
                Log::channel('olx_scraper')->error("HTTP error fetching {$url}: {$e->getMessage()}");
            }

            $attempt++;
        } while ($attempt < $maxRetries);

        Log::channel('olx_scraper')->error("Max retries reached for {$url}");
        return null;
    }

    // Helper methods for extraction
    private function extractAttributes($node) {
        $attributes = [];

        $node->filter('div[data-testid="ad-parameters-container"] p.css-1los5bp')->each(function ($attrNode) use (&$attributes) {
            $text = $attrNode->text();

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

    protected function extractJsonData(Crawler $propertyPage): ?array
    {
        try {
            $scriptNodes = $propertyPage->filter('script[type="application/ld+json"]');

            if ($scriptNodes->count() === 0) {
                file_put_contents('debug.html', $propertyPage->html());
                Log::channel('olx_scraper')->warning("No JSON-LD script found on page");
                return null;
            }

            $jsonContent = $scriptNodes->first()->text();
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::channel('olx_scraper')->warning("Failed to parse JSON data: " . json_last_error_msg());
                return null;
            }

            return $jsonData;
        } catch (\Exception $e) {
            Log::channel('olx_scraper')->warning("Error extracting JSON data: " . $e->getMessage());
            return null;
        }
    }

}
