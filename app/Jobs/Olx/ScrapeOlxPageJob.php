<?php

namespace App\Jobs\Olx;

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
            Log::error("Failed to fetch OLX page {$this->page} at {$url}");
            $this->release($this->backoff);
            return;
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $ads = $crawler->filter('div[data-cy="l-card"]');

        if ($ads->count() === 0) {
            Log::info("No ads found on page {$this->page} for {$this->baseUrl}");
            return;
        }

        $ads->each(function (Crawler $adNode) {
            try {
                $title = trim($adNode->filter('h6')->text(''));
                $url = $adNode->filter('a')->attr('href') ?? '';
                $price = $adNode->filter('p[data-testid="ad-price"]')->text('');
                $location = $adNode->filter('p[data-testid="location-date"]')->text('');
                $imageUrl = $adNode->filter('img')->attr('src') ?? null;

                if (!$url || !$title) return;

                ImportPropertyJob::dispatch([
                    'title'    => $title,
                    'url'      => $url,
                    'price'    => $price,
                    'location' => $location,
                    'category' => $this->category,
                ]);

                if ($imageUrl) {
                    DownloadAndSaveImageJob::dispatch($url, $imageUrl);
                }

            } catch (\Throwable $e) {
                Log::error("Error scraping individual ad: {$e->getMessage()}");
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
                    'User-Agent' => fake()->userAgent(),
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])->timeout(10)
                    ->get($url);

                if ($response->status() === 403) {
                    Log::warning("OLX 403 Forbidden: {$url} - Backing off longer");
                    sleep(rand(10, 30));
                    return null;
                }

                if ($response->successful()) {
                    return $response;
                }

                Log::warning("Failed to fetch {$url} with status {$response->status()}");

            } catch (\Throwable $e) {
                Log::error("HTTP error fetching {$url}: {$e->getMessage()}");
            }

            $attempt++;
        } while ($attempt < $maxRetries);

        Log::error("Max retries reached for {$url}");
        return null;
    }
}
