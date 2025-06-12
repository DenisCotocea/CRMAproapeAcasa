<?php

namespace App\Jobs\Storia;

use App\Models\Image;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadAndSaveImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $propertyUrl,
        protected string $imageUrl,
        protected string $propertyId
    ) {}

    public function handle(): void
    {
        try {
            $response = Http::timeout(10)->get($this->imageUrl);

            if (!$response->successful()) {
                Log::channel('storia_scraper')->warning("Image download failed for {$this->propertyUrl} - Status: {$response->status()}");
                return;
            }

            $extension = pathinfo(parse_url($this->imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'storia/' . md5($this->imageUrl) . '.' . $extension;

            Storage::disk('public')->put($filename, $response->body());

            Image::create([
                'entity_id' => $this->propertyId,
                'entity_type' => Property::class,
                'path' => $filename,
            ]);

            Log::channel('storia_scraper')->info("Downloaded image for {$this->propertyUrl} -> $filename");

        } catch (\Throwable $e) {
            Log::channel('storia_scraper')->error("DownloadAndSaveImageJob error: " . $e->getMessage());
        }
    }
}
