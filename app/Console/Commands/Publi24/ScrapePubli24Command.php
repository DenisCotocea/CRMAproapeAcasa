<?php

namespace App\Console\Commands\Publi24;

use Illuminate\Console\Command;
use App\Services\Publi24\Publi24ScraperService;
class ScrapePubli24Command extends Command
{
    protected $signature = 'scrape:publi24';
    protected $description = 'Scrape Publi24 for all categories (dynamic pagination)';

    public function __construct(protected Publi24ScraperService $scraper)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info("Dispatching dynamic scraping jobs for all Publi24 categories...");
        $this->scraper->scrapeAllCategories();
        $this->info("Jobs dispatched successfully.");
    }
}
