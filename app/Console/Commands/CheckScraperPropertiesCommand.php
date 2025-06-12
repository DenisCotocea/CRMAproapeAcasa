<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\CheckScraperPropertiesService;

class CheckScraperPropertiesCommand extends Command {
    protected $signature = 'verify:properties';
    protected $description = 'Verify scraper properties if url dosen t work deactivate it';

    public function handle() {

        $verify = new CheckScraperPropertiesService();
        Log::channel('delisted_scraper')->info("Starting Verifying properties...");

        $verify->checkPropertiesActiveStatus();

        Log::channel('delisted_scraper')->info("Completed Verifying properties...");
    }
}
