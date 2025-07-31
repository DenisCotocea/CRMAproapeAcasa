<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OlxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('OLX Webhook Received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        return response()->json(['status' => 'ok'], 200);
    }
}
