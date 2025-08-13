<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\Property;
use Illuminate\Support\Facades\Log;

class OlxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $headers = $request->headers->all();

            Log::channel('olx_apis')->info('OLX Webhook Received', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $headers,
                'payload' => $payload,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            $url = $payload['data']['url'] ?? null;

            if ($url) {
                $property = Property::where('uuid', $payload['object_id'])->first();

                if ($property) {
                    $property->storia_url = $url;
                    $property->save();
                }
            }

            $this->processNotification($payload);

            return response()->json(['status' => 'ok'], 200);
        } catch (Exception $e) {
            Log::channel('olx_apis')->error('OLX Webhook Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all() ?? 'Unable to get payload'
            ]);

            return response()->json(['status' => 'ok'], 200);
        }
    }

    /**
     * Process different types of OLX notifications
     */
    private function processNotification(array $payload)
    {
        if (!isset($payload['type'])) {
            Log::channel('olx_apis')->warning('OLX Webhook: Missing notification type', ['payload' => $payload]);
            return;
        }

        $notificationType = $payload['type'];

        switch ($notificationType) {
            case 'advert_status_changed':
                $this->logAdvertStatusChanged($payload);
                break;

            case 'message_received':
                $this->logMessageReceived($payload);
                break;

            case 'advert_expired':
                $this->logAdvertExpired($payload);
                break;

            case 'advert_rejected':
                $this->logAdvertRejected($payload);
                break;

            case 'advert_approved':
                $this->logAdvertApproved($payload);
                break;

            default:
                Log::channel('olx_apis')->info('OLX Webhook: Unknown notification type', [
                    'type' => $notificationType,
                    'payload' => $payload
                ]);
        }
    }

    /**
     * Log advert status change notifications
     */
    private function logAdvertStatusChanged(array $payload)
    {
        Log::channel('olx_apis')->info('OLX Notification: Advert Status Changed', [
            'advert_id' => $payload['advert_id'] ?? 'unknown',
            'old_status' => $payload['old_status'] ?? 'unknown',
            'new_status' => $payload['new_status'] ?? 'unknown',
            'reason' => $payload['reason'] ?? null,
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'full_payload' => $payload
        ]);
    }

    /**
     * Log message received notifications
     */
    private function logMessageReceived(array $payload)
    {
        Log::channel('olx_apis')->info('OLX Notification: Message Received', [
            'advert_id' => $payload['advert_id'] ?? 'unknown',
            'message_id' => $payload['message_id'] ?? 'unknown',
            'sender' => $payload['sender'] ?? 'unknown',
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'full_payload' => $payload
        ]);
    }

    /**
     * Log advert expired notifications
     */
    private function logAdvertExpired(array $payload)
    {
        Log::channel('olx_apis')->info('OLX Notification: Advert Expired', [
            'advert_id' => $payload['advert_id'] ?? 'unknown',
            'expiry_date' => $payload['expiry_date'] ?? 'unknown',
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'full_payload' => $payload
        ]);
    }

    /**
     * Log advert rejected notifications
     */
    private function logAdvertRejected(array $payload)
    {
        Log::channel('olx_apis')->info('OLX Notification: Advert Rejected', [
            'advert_id' => $payload['advert_id'] ?? 'unknown',
            'reason' => $payload['reason'] ?? 'unknown',
            'rejection_details' => $payload['rejection_details'] ?? null,
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'full_payload' => $payload
        ]);
    }

    /**
     * Log advert approved notifications
     */
    private function logAdvertApproved(array $payload)
    {
        Log::channel('olx_apis')->info('OLX Notification: Advert Approved', [
            'advert_id' => $payload['advert_id'] ?? 'unknown',
            'approval_date' => $payload['approval_date'] ?? 'unknown',
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'full_payload' => $payload
        ]);
    }
}
