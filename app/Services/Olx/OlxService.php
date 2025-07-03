<?php

namespace App\Services\Olx;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OlxService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.olx.client_id');
        $this->clientSecret = config('services.olx.client_secret');
        $this->redirectUri = config('services.olx.redirect_uri');
    }

    public function getAuthUrl()
    {
        return 'https://www.olx.ro/api/open/oauth/authorize?' . http_build_query([
                'client_id' => config('services.olx.client_id'),
                'response_type' => 'code',
                'redirect_uri' => config('services.olx.redirect_uri'),
            ]);
    }

    public function getToken($code)
    {
        $response = Http::asForm()->post('https://www.olx.ro/api/open/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.olx.redirect_uri'),
            'client_id' => config('services.olx.client_id'),
            'client_secret' => config('services.olx.client_secret'),
        ]);

        $data = $response->json();

        Cache::put('olx_access_token', $data['access_token'], $data['expires_in']);
        Cache::put('olx_refresh_token', $data['refresh_token']);

        return $data;
    }

    public function refreshToken()
    {
        $refreshToken = Cache::get('olx_refresh_token');

        $response = Http::asForm()->post('https://www.olx.ro/api/open/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('services.olx.client_id'),
            'client_secret' => config('services.olx.client_secret'),
        ]);

        $data = $response->json();

        Cache::put('olx_access_token', $data['access_token'], $data['expires_in']);
        Cache::put('olx_refresh_token', $data['refresh_token']);

        return $data;
    }

    public function postAd(array $adData)
    {
        $accessToken = Cache::get('olx_access_token');

        $response = Http::withToken($accessToken)
            ->post(config('services.olx.api_url') . '/partner/adverts', $adData);

        return $response->json();
    }
}
