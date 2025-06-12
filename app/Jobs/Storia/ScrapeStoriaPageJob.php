<?php

namespace App\Jobs\Storia;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeStoriaPageJob implements ShouldQueue
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
            Log::channel('storia_scraper')->error("Failed to fetch Storia page {$this->page} at {$url}");
            $this->release($this->backoff);
            return;
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $ads = $crawler->filter('article[data-cy="listing-item"]');

        if ($ads->count() === 0) {
            Log::channel('storia_scraper')->info("No ads found on page {$this->page} for {$url}");
            return;
        }

        $ads->each(function (Crawler $adNode){
            try {
                $propertyUrl = $adNode->filter('a[data-cy="listing-item-link"]')->attr('href');
                $propertyUrl = 'https://www.storia.ro' . $propertyUrl;

                $response = $this->getWithRetries($propertyUrl);

                $propertyPage = new Crawler($response->body());

                $data = $this->extractData($propertyPage);

                $ad = $data['props']['pageProps']['ad'] ?? null;
                if (!$ad) {
                    Log::channel('storia_scraper')->warning("Ad data missing, skipping.");
                    return;
                }

                if (!empty($ad['agency'])) {
                    Log::channel('storia_scraper')->info("Skipped agency-listed property with ID: " . ($ad['id'] ?? 'unknown'));
                    return;
                }

                $storiaId = $ad['id'] ?? null;

                if (Property::where('scraper_link', $propertyUrl)->orWhere('scraper_code', $storiaId)->exists()) {
                    Log::channel('storia_scraper')->info("Property already exists: {$propertyUrl} or Storia ID: {$storiaId}");
                    return;
                }

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

                $attributes = [
                    'roomsNumber' => $roomsNumber,
                    'floor' => $floor,
                    'surfaceArea' => $surfaceArea,
                    'totalFloors' => $totalfloors,
                    'year' => $constractionYear,
                ];

                ImportPropertyJob::dispatch([
                    'storia_id' => $storiaId,
                    'title'    => $name,
                    'url'      => $propertyUrl,
                    'price'    => $price ?? 0,
                    'description'    => $description,
                    'images' => json_encode($imageLinks),
                    'attributes'    => json_encode($attributes),
                    'county' => $county,
                    'city' => $city,
                    'address' => $address,
                    'transaction' => $transaction,
                    'type' => $propertyType,
                ]);
            } catch (\Throwable $e) {
                Log::channel('storia_scraper')->error("Error scraping individual ad: {$e->getMessage()}");
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
                ])->timeout(15)->get($url);

                if ($response->status() === 403) {
                    Log::channel('storia_scraper')->warning("Publi24 403 Forbidden: {$url} - Backing off longer");
                    sleep(rand(10, 30));
                    return null;
                }

                if ($response->successful()) {
                    return $response;
                }

                Log::channel('storia_scraper')->warning("Failed to fetch {$url} with status {$response->status()}");

            } catch (\Throwable $e) {
                Log::channel('storia_scraper')->error("HTTP error fetching {$url}: {$e->getMessage()}");
            }

            $attempt++;
        } while ($attempt < $maxRetries);

        Log::channel('storia_scraper')->error("Max retries reached for {$url}");
        return null;
    }

    // Helper methods for extraction
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
