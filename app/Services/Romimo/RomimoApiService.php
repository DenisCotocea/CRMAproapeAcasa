<?php

namespace App\Services\Romimo;

use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

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
        try {
            return Cache::remember('romimo_token', 23 * 60, function () {
                $response = Http::withHeaders([
                    'x-api-version' => '2',
                ])->post("{$this->baseUrl}/api/Token?ApiKey={$this->apiKey}");

                if ($response->successful()) {
                    Log::channel('romimo_apis')->debug('Romimo API Token fetched successfully.');
                    return $response->body();
                }

                Log::channel('romimo_apis')->warning('Failed to fetch Romimo API Token.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            });
        } catch (Exception $e) {
            Log::channel('romimo_apis')->error('Error fetching Romimo API Token.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function createOrUpdateArticle(array $payload): array
    {
        try {
            $token = $this->getToken();

            if (!$token) {
                return [
                    'status' => 500,
                    'body' => ['error' => 'Romimo API Token is missing.'],
                ];
            }

            Log::channel('romimo_apis')->debug('Sending payload to Romimo API.', [
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'x-api-version' => '2',
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/Article", $payload);

            $result = [
                'status' => $response->status(),
                'body' => $response->json(),
            ];

            Log::channel('romimo_apis')->debug('Received response from Romimo API.', [
                'response' => $result,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::channel('romimo_apis')->error('Error sending payload to Romimo API.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 500,
                'body' => ['error' => 'There was an error: ' . $e->getMessage()],
            ];
        }
    }

    public function deactivateRomimo(Property $property): array
    {
        try {
            $token = $this->getToken();

            if (!$token) {
                return [
                    'status' => 500,
                    'body' => ['detail' => 'Romimo API token is missing.'],
                ];
            }

            $email = $property->user->email;
            $externalId = $property->unique_code;

            $url = "{$this->baseUrl}/api/Article?Email={$email}&ExternalId={$externalId}";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'x-api-version' => '2',
            ])->delete($url);

            return [
                'status' => $response->status(),
                'body' => $response->status() !== 204 ? $response->json() : [],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'body' => ['detail' => $e->getMessage()],
            ];
        }
    }

    public function getCategories()
    {
        try {
            $token = $this->getToken();

            if (!$token) {
                return [
                    'status' => 500,
                    'body' => ['error' => 'Romimo API Token is missing.'],
                ];
            }


            $response = Http::withHeaders([
                'x-api-version' => '2',
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/api/Resources/Categories");

            dd($response->json());

            return [
                'status' => $response->status(),
                'body' => $response->json(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'body' => ['error' => 'There was an error: ' . $e->getMessage()],
            ];
        }
    }

    public function getProperties()
    {
        try {
            $token = $this->getToken();

            if (!$token) {
                return [
                    'status' => 500,
                    'body' => ['error' => 'Romimo API Token is missing.'],
                ];
            }

            $response = Http::withHeaders([
                'x-api-version' => '2',
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/api/Resources/Properties");

            dd($response->json());

            return [
                'status' => $response->status(),
                'body' => $response->json(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'body' => ['error' => 'There was an error: ' . $e->getMessage()],
            ];
        }
    }
}
