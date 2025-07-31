<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OlxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
