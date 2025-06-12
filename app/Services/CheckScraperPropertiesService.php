<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Illuminate\Support\Facades\Log;
use App\Models\Property;

class CheckScraperPropertiesService
{
    protected $client;

    public function __construct() {
        $this->client = HttpClient::create([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ]
        ]);
    }

    public function checkPropertiesActiveStatus()
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            try {
                $response = $this->client->request('GET', $property->scraper_link);

                if ($response->getStatusCode() == 410 || $response->getStatusCode() == 404) {
                    $property->active = 0;
                    $property->save();

                    Log::channel('delisted_scraper')->info("Property ID {$property->id} deactivated because it was deleted.");
                    continue;
                }

                $content = $response->getContent();

                if ($this->isPropertyRemoved($content)) {
                    $property->active = 0;
                    $property->save();
                    Log::channel('delisted_scraper')->info("Property ID {$property->id} deactivated due to removed listing text.");
                } else {

                    if ($property->active === 0) {
                        $property->active = 1;
                        $property->save();
                        Log::channel('delisted_scraper')->info("Property ID {$property->id} reactivated as URL is accessible.");
                    }
                }
            } catch (\Exception $e) {
                Log::channel('delisted_scraper')->error("Failed to check property ID {$property->id}: {$e->getMessage()}");
            }

            sleep(2);
        }
    }

    private function isPropertyRemoved(string $htmlContent): bool
    {
        return str_contains($htmlContent, 'Anunțul nu mai este valabil')
            || str_contains($htmlContent, 'Listing removed')
            || str_contains($htmlContent, '404 Not Found')
            || str_contains($htmlContent, 'Pagina cautata nu a fost gasita')
            || str_contains($htmlContent, 'Page not found');
    }
}
