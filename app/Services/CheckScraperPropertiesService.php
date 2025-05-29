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
        $this->client = HttpClient::create();
    }

    public function checkPropertiesActiveStatus()
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            try {
                $response = $this->client->request('GET', $property->scraper_link);

                if ($response->getStatusCode() == 410) {
                    $property->active = 0;
                    $property->save();

                    Log::channel('daily')->info("Property ID {$property->id} deactivated because it was deleted.");
                    continue;
                }

                $content = $response->getContent();

                if ($this->isPropertyRemoved($content)) {
                    $property->active = 0;
                    $property->save();
                    Log::channel('daily')->info("Property ID {$property->id} deactivated due to removed listing text.");
                } else {

                    if ($property->active === 0) {
                        $property->active = 1;
                        $property->save();
                        Log::channel('daily')->info("Property ID {$property->id} reactivated as URL is accessible.");
                    }
                }
            } catch (\Exception $e) {
                Log::channel('daily')->error("Failed to check property ID {$property->id}: {$e->getMessage()}");
            }

            sleep(1);
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
