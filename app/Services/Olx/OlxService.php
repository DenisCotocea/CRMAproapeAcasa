<?php

namespace App\Services\Olx;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OlxService
{
    protected $urn;

    protected $apiKey;

    protected $basic64;

    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->urn = config('services.olx.urn');
        $this->apiKey = config('services.olx.api_key');
        $this->basic64 = config('services.olx.basic_64');
        $this->clientId = config('services.olx.client_id');
        $this->clientSecret = config('services.olx.client_secret');
    }

    public function getToken()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->basic64,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.olxgroup.com/oauth/v1/token', [
            'grant_type' => 'authorization_code',
            'code' => '4e6baca800f3867679c029d34949e7d103b2c891'
        ]);

        $data = $response->json();

        Cache::put('olx_access_token', $data['access_token'], $data['expires_in']);
        Cache::put('olx_refresh_token', $data['refresh_token']);

        return $data;
    }

    public function refreshToken()
    {
        $refreshToken = Cache::get('olx_refresh_token');

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->basic64,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.olxgroup.com/oauth/v1/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);

        $data = $response->json();

        Cache::put('olx_access_token', $data['access_token'], $data['expires_in']);
        Cache::put('olx_refresh_token', $data['refresh_token']);

        return $data;
    }

    protected function getAccessToken()
    {
        return Cache::get('olx_access_token');
    }

    public function postAd(array $adData)
    {
        if($this->getAccessToken() === null) {
            $this->refreshToken();
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.olxgroup.com/advert/v1', $adData);

        return $response->json();
    }

    public function updateAd(string $adId, array $adData)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->put(config('services.olx.api_url') . "/partner/adverts/{$adId}", $adData);

        return $response->json();
    }

    public function deleteAd(string $adId)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->delete(config('services.olx.api_url') . "/partner/adverts/{$adId}");

        return $response->json();
    }

    public function activateAd(string $adId)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("https://api.olxgroup.com" . "/adverts/v1/{$adId}/activate");

        return $response->json();
    }

    public function deactivateAd(string $adId)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post(config('services.olx.api_url') . "/partner/adverts/{$adId}/deactivate");

        return $response->json();
    }

    public function applyPromotion(string $adId, array $promotionData)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post(config('services.olx.api_url') . "/partner/adverts/{$adId}/promote", $promotionData);

        return $response->json();
    }

    public function getSiteTaxonomy()
    {
        $apiKey = config('services.olx.api_key');

        $siteUrn = 'urn:site:storiaro';
        $encodedUrn = urlencode($siteUrn);

        $url = "https://api.olxgroup.com/taxonomy/v1/categories/partner/{$encodedUrn}";

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $apiKey,
        ])->get($url);

        return $response->json();
    }

    public function getRequiredAttributesForStoriaCategory()
    {
        $apiKey = config('services.olx.api_key');

        $siteUrn = 'urn:site:storiaro';
        $categoryUrn = 'urn:concept:apartments-for-sale';

        $url = 'https://api.olxgroup.com/taxonomy/v1/categories/partner/' .
            urlencode($siteUrn) . '/' . urlencode($categoryUrn);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $apiKey,
        ])->get($url);

        dd($response->json());

        return [
            'success' => false,
            'status' => $response->status(),
            'error' => $response->json(),
        ];
    }
}
