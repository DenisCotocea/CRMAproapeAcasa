<?php

namespace App\Console\Commands\Olx;

use Illuminate\Console\Command;
use App\Services\Olx\OlxScraperService;
class ScrapeOlxCommand extends Command
{
    protected $signature = 'scrape:olx';
    protected $description = 'Scrape OLX for all categories (dynamic pagination)';

    public function __construct(protected OlxScraperService $scraper)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info("Dispatching dynamic scraping jobs for all OLX categories...");
        $this->scraper->scrapeAllCategories();
        $this->info("Jobs dispatched successfully.");
    }
}
