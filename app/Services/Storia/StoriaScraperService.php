<?php

namespace App\Services\Storia;

use App\Jobs\Publi24\ScrapePubli24PageJob;
use App\Jobs\Storia\ScrapeStoriaPageJob;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class StoriaScraperService
{
    protected array $urls = [
        'apartament_rent' => 'https://www.storia.ro/ro/rezultate/inchiriere/apartament/brasov',
        'apartament_buy'  => 'https://www.storia.ro/ro/rezultate/vanzare/apartament/brasov',
        'garsoniera_rent' => 'https://www.storia.ro/ro/rezultate/inchiriere/garsoniere/brasov',
        'garsoniera_buy'  => 'https://www.storia.ro/ro/rezultate/vanzare/garsoniere/brasov',
        'house_buy'       => 'https://www.storia.ro/ro/rezultate/vanzare/casa/brasov',
        'land_buy'        => 'https://www.storia.ro/ro/rezultate/vanzare/teren/brasov',
    ];

    public function scrapeAllCategories(): void
    {
        foreach ($this->urls as $category => $baseUrl) {
            $lastPage = $this->getLastPageNumber($baseUrl);
            for ($page = 1; $page <= $lastPage; $page++) {
                ScrapeStoriaPageJob::dispatch($baseUrl, $page, $category)->onQueue('scraping');
            }
        }
    }

    protected function getLastPageNumber(string $url): int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])->timeout(15)->get($url);

            $body = $response->body();

            $lastPage = 1;

            if (preg_match('/"pagination":\s*({.*?"totalPages":\d+.*?})/', $body, $matches)) {
                $paginationData = json_decode($matches[1], true);
                if (isset($paginationData['totalPages'])) {
                    return (int) $paginationData['totalPages'];
                }
            }

            return $lastPage;
        } catch (\Throwable $e) {
            Log::channel('storia_scraper')->error("Error getting total pages for URL: $url", ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
