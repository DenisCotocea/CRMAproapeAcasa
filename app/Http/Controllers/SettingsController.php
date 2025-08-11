<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');

        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return redirect()->route('settings.index')->with('success', 'Cache cleared successfully!');
    }

    public function runAgentSync(){
        Artisan::call('app:sync-imobiliare-agents');
        return redirect()->route('settings.index')->with('success', 'Agents imported successfully!');
    }

    public function refreshOlxToken()
    {
        $clientId = env('OLX_CLIENT_ID');
        $state = 'securetoken';

        $url = "https://storia.ro/ro/crm/authorization?response_type=code&client_id={$clientId}&state={$state}";

        return redirect()->away($url);
    }
}
