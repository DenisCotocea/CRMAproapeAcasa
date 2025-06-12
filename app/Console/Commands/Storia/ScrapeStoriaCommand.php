<?php

namespace App\Console\Commands\Storia;

use Illuminate\Console\Command;
use App\Services\Storia\StoriaScraperService;
class ScrapeStoriaCommand extends Command
{
    protected $signature = 'scrape:storia';
    protected $description = 'Scrape Storia for all categories (dynamic pagination)';

    public function __construct(protected StoriaScraperService $scraper)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info("Dispatching dynamic scraping jobs for all Storia categories...");
        $this->scraper->scrapeAllCategories();
        $this->info("Jobs dispatched successfully.");
    }
}
