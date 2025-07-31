<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OlxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        $signature = $request->header('x-signature');
        $secret = config('services.olx.webhook_secret');

        $objectId = $data['object_id'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $expectedSignature = hash_hmac('sha1', "{$objectId},{$transactionId}", $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('OLX webhook: invalid signature', compact('data', 'signature', 'expectedSignature'));
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        Log::info('OLX webhook received', [
            'transaction_id' => $transactionId,
            'event_type' => $data['event_type'] ?? '',
            'data' => $data,
        ]);


        return response()->json(['status' => 'ok'], 200);
    }
}
