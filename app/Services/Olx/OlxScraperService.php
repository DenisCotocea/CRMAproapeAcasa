<?php

namespace App\Services\Olx;

use App\Jobs\Olx\ScrapeOlxPageJob;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class OlxScraperService
{
    protected array $urls = [
        'apartament_rent' => 'https://www.olx.ro/imobiliare/apartamente-garsoniere-de-inchiriat/brasov/',
        'apartament_buy'  => 'https://www.olx.ro/imobiliare/apartamente-garsoniere-de-vanzare/brasov/',
        'house_buy'       => 'https://www.olx.ro/imobiliare/case-de-vanzare/brasov/',
        'land_buy'        => 'https://www.olx.ro/imobiliare/terenuri/brasov/',
    ];

    public function scrapeAllCategories(): void
    {
        foreach ($this->urls as $category => $baseUrl) {
//            $lastPage = $this->getLastPageNumber($baseUrl);
            $lastPage = 25;
            for ($page = 1; $page <= $lastPage; $page++) {
                ScrapeOlxPageJob::dispatch($baseUrl, $page, $category)->onQueue('scraping');
            }
        }
    }

    # FUNCTION NOT NEEDED AT THE MOMENT

//    protected function getLastPageNumber(string $url): int
//    {
//        try {
//            $response = Http::get($url);
//            $crawler = new Crawler($response->body());
//
//
//            $pageLinks = $crawler->filter('a[data-testid="pagination-forward"]')->each(function ($node) {
//                return (int) filter_var($node->text(), FILTER_SANITIZE_NUMBER_INT);
//            });
//            $maxPage = collect($pageLinks)->filter()->max();
//
//            return 25;
//        } catch (\Throwable $e) {
//            Log::channel('olx_scraper')->error("Error getting total pages for URL: $url", ['error' => $e->getMessage()]);
//            return 1;
//        }
//    }
}
