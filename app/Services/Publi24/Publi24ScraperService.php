<?php

namespace App\Services\Publi24;

use App\Jobs\Olx\ScrapeOlxPageJob;
use App\Jobs\Publi24\ScrapePubli24PageJob;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class Publi24ScraperService
{
    protected array $urls = [
        'apartament_rent' => 'https://www.publi24.ro/anunturi/imobiliare/de-inchiriat/apartamente/brasov/?commercial=false',
        'apartament_buy'  => 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/apartamente/brasov/?commercial=false',
        'garsoniera_rent' => 'https://www.publi24.ro/anunturi/imobiliare/de-inchiriat/apartamente/garsoniera/brasov/?commercial=false',
        'garsoniera_buy'  => 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/apartamente/garsoniera/brasov/?commercial=false',
        'house_buy'       => 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/case/brasov/?commercial=false',
        'land_buy'        => 'https://www.publi24.ro/anunturi/imobiliare/de-vanzare/terenuri/brasov/?commercial=false',
    ];

    public function scrapeAllCategories(): void
    {
        foreach ($this->urls as $category => $baseUrl) {
            $lastPage = $this->getLastPageNumber($baseUrl);
            for ($page = 1; $page <= $lastPage; $page++) {
                ScrapePubli24PageJob::dispatch($baseUrl, $page, $category)->onQueue('scraping');
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

            $crawler = new Crawler($response->body());

            $lastPage = 1;

            $crawler->filter('ul.pagination li a')->each(function ($node) use (&$lastPage) {
                $text = trim($node->text());

                if (is_numeric($text)) {
                    $number = (int) $text;
                    if ($number > $lastPage) {
                        $lastPage = $number;
                    }
                }
            });

            return $lastPage;
        } catch (\Throwable $e) {
            Log::channel('publi24_scraper')->error("Error getting total pages for URL: $url", ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
