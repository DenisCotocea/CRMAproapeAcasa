<?php

namespace App\Jobs\Olx;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportPropertyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data) {}

    public function handle(): void
    {
        try {
            if (Property::where('url', $this->data['url'])->exists()) {
                Log::info("Property already exists: " . $this->data['url']);
                return;
            }

            Property::create([
                'title'    => $this->data['title'],
                'url'      => $this->data['url'],
                'price'    => $this->data['price'],
                'location' => $this->data['location'],
                'category' => $this->data['category'],
                'user_id'  => 1,
            ]);

            Log::info("Imported property: " . $this->data['url']);
        } catch (\Throwable $e) {
            Log::error("ImportPropertyJob error: " . $e->getMessage());
        }
    }
}
