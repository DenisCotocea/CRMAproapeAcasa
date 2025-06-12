<?php

namespace App\Jobs\Publi24;

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
            if($this->data['county'] === 'Brasov') {
                $attributes = json_decode($this->data['attributes'], true);
                $images = json_decode($this->data['images'], true);
                $property = Property::create([
                    'name' => $this->data['title'],
                    'type' => $this->data['type'],
                    'tranzaction'  => $this->data['transaction'],
                    'county' => $this->data['county'],
                    'city' => $this->data['city'],
                    'address' => $this->data['address'],
                    'price' => $this->data['price'],
                    'description' =>  $this->data['description'],
                    'from_scraper' => 'Publi24',
                    'scraper_link' => $this->data['url'],
                    'unique_code' => str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'usable_area' => $this->extractUsableArea($attributes['Suprafata utila'] ?? null),
                    'floor' => $this->extractFloor($attributes['Etaj'] ?? null),
                    'partitioning' => $attributes['Compartimentare'] ?? null,
                    'room_numbers' => $this->extractRooms($attributes['Numar camere'] ?? null),
                ]);

                foreach ($images as $image) {
                    DownloadAndSaveImageJob::dispatch($this->data['url'], $image, $property->id);
                }

                Log::channel('publi24_scraper')->info("Imported property: " . $this->data['url']);
            }else{
                Log::channel('publi24_scraper')->info("Property is not from the  right County: " . $this->data['url']);
                return;
            }
        } catch (\Throwable $e) {
            Log::channel('publi24_scraper')->error("ImportPropertyJob error: " . $e->getMessage());
        }
    }

    private function extractUsableArea($usableAreaText) {
        if (empty($usableAreaText)) {
            return null;
        }

        if (preg_match('/[\d.]+/', $usableAreaText, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }

    private function extractRooms($roomsText) {
        if (empty($roomsText)) {
            return null;
        }

        if (preg_match('/\d+/', $roomsText, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function extractFloor($floorText) {
        if (empty($floorText)) {
            return null;
        }

        $floorText = strtolower(trim($floorText));

        if (strpos($floorText, 'parter') !== false) {
            return 0;
        }

        if (preg_match('/\d+/', $floorText, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }
}
