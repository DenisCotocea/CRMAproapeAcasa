<?php

namespace App\Jobs\Storia;

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
            $attributes = json_decode($this->data['attributes'], true);
            $images = json_decode($this->data['images'], true);
            $property = Property::create([
                'name' => $this->data['title'],
                'type' => $this->data['type'],
                'tranzaction'  => $this->data['transaction'],
                'county' => $this->data['county'] ?? 'Brasov',
                'city' => $this->data['city'] ?? 'Brasov',
                'address' => $this->data['address'],
                'price' => $this->data['price'],
                'description' =>  $this->data['description'],
                'from_scraper' => 'Storia',
                'scraper_link' => $this->data['url'],
                'scraper_code' => $this->data['storia_id'],
                'unique_code' => str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'room_numbers' => $attributes['roomsNumbers'] ?? null,
//                    'floor' => $attributes['floor'] ?? null,
                'total_floors' => $attributes['totalFloors'] ?? null,
                'construction_year' => $attributes['year'] ?? null,
                'usable_area' => $attributes['surfaceArea'] ?? null,
            ]);

            foreach ($images as $image) {
                \App\Jobs\Storia\DownloadAndSaveImageJob::dispatch($this->data['url'], $image, $property->id);
            }

            Log::channel('storia_scraper')->info("Imported property: " . $this->data['url']);
        } catch (\Throwable $e) {
            Log::channel('storia_scraper')->error("ImportPropertyJob error: " . $e->getMessage());
        }
    }
}
