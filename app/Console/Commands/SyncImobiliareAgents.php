<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Imobiliare\Apis\ImobiliareApiService;
use Illuminate\Support\Facades\Log;

class SyncImobiliareAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-imobiliare-agents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify imobiliare.ro agents';

    protected ImobiliareApiService $imobiliareApiService;

    public function __construct(ImobiliareApiService $imobiliareApiService)
    {
        parent::__construct();
        $this->imobiliareApiService = $imobiliareApiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('imobiliare_apis')->warning("Starting Agent sync...");
        try {
            $this->imobiliareApiService->syncAllAgents();
            Log::channel('imobiliare_apis')->info('Agent sync completed successfully.');
            return true;
        } catch (\Exception $e) {
            Log::channel('imobiliare_apis')->error('Agent sync failed: ' . $e->getMessage());
            return false;
        }
    }
}
