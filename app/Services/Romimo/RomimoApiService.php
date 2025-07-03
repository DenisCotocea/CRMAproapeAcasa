<?php

namespace App\Services\Romimo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RomimoApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.romimo.api_key');
        $this->baseUrl = 'https://services.romimo.ro';
    }

    public function getToken(): ?string
    {
        return Cache::remember('romimo_token', 23 * 60, function () {
            $response = Http::withHeaders([
                'x-api-version' => '2',
            ])->post("{$this->baseUrl}/api/Token", [
                'ApiKey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        });
    }

    public function createOrUpdateArticle(array $payload): array
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'x-api-version' => '2',
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/Article", $payload);

        return [
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }
}
