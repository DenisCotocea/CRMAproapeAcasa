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
            $attributes = json_decode($this->data['attributes'], true);
            $property = Property::create([
                'name' => $this->data['title'],
                'type' => $this->data['type'],
                'tranzaction'  => $this->data['transaction'],
                'county' => $this->data['county'],
                'city' => $this->data['city'],
                'address' => $this->data['address'],
                'price' => $this->data['price'],
                'description' =>  $this->data['description'],
                'from_scraper' => 'Olx',
                'scraper_link' => $this->data['url'],
                'scraper_code' => $this->data['olx_id'],
                'unique_code' => str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'construction_year' => $this->extractConstructionYear($attributes['An constructie'] ?? null),
                'usable_area' => $this->extractUsableArea($attributes['Suprafata utila'] ?? null),
                'floor' => $this->extractFloor($attributes['Etaj'] ?? null),
                'partitioning' => $attributes['Compartimentare'] ?? null,
            ]);

            foreach ($this->data['images'] as $image) {
                DownloadAndSaveImageJob::dispatch($this->data['url'], $image, $property->id);
            }

            Log::channel('olx_scraper')->info("Imported property: " . $this->data['url']);
        } catch (\Throwable $e) {
            Log::channel('olx_scraper')->error("ImportPropertyJob error: " . $e->getMessage());
        }
    }

    private function extractConstructionYear($constructionYearText) {
        if (empty($constructionYearText)) {
            return null;
        }

        if (preg_match('/\b(\d{4})\b/', $constructionYearText, $matches)) {
            return (int) $matches[1];
        }

        return null;
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

    private function extractFloor($floorText) {
        if (empty($floorText)) {
            return null;
        }

        $floorText = strtolower(trim($floorText));

        $floorMapping = [
            'parter' => 0,
            'demisol' => -1,
            'subsol' => -2,
            '10 sau peste' => 10
        ];

        if (array_key_exists($floorText, $floorMapping)) {
            return $floorMapping[$floorText];
        }

        if (is_numeric($floorText)) {
            return (int)$floorText;
        }

        return null;
    }
}
