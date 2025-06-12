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
            $scriptPath = base_path('scripts/puppeteer-publi24.cjs');

            $escapedUrl = escapeshellarg($url);

            $command = "node {$scriptPath} {$escapedUrl}";

            exec($command, $output, $return_var);

            if ($return_var !== 0 || empty($output)) {
                throw new \Exception("Failed to fetch HTML via Puppeteer");
            }

            $html = implode("\n", $output);

            $crawler = new Crawler($html);

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
