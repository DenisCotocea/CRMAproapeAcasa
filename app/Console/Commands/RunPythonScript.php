<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunPythonScript extends Command
{
    protected $signature = 'python:run-script';

    // The console command description.
    protected $description = 'Run a Python script from Laravel';

    // Execute the console command.
    public function handle()
    {
        $this->info('Running Python script...');

        // Path to your Python script
        $command = 'python3 ' . base_path('app/python/scripts/your_script.py');

        // Run the command and capture output
        $output = shell_exec($command);

        if ($output === null) {
            $this->error('Failed to execute Python script.');
        } else {
            $this->info('Python script executed successfully:');
            $this->line($output);
        }
    }
}
