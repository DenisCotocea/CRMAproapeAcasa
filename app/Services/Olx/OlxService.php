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

    public function getToken($code)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->basic64,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.olxgroup.com/oauth/v1/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
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

    public function getAccessToken()
    {
        return Cache::get('olx_access_token');
    }

    public function getRefreshToken()
    {
        return Cache::get('olx_refresh_token');
    }

    public function postAd(array $adData)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.olxgroup.com/advert/v1', $adData);

        return $response;
    }

    public function deactivateAd(string $adId)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]) ->post("https://api.olxgroup.com/advert/v1/{$adId}/deactivate");

        return $response;
    }
}
