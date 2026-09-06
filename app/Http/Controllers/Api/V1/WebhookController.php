<?php

namespace App\Http\Controllers\Api\V1;

use App\Billing\Contracts\PaymentProviderInterface;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function store(Request $request, PaymentProviderInterface $payments): JsonResponse
    {
        $payload = $request->all();

        if (! $payments->verifyWebhook($payload, $request->header('X-CommercePilot-Webhook-Signature'))) {
            return response()->json([
                'error' => [
                    'code' => 'invalid_webhook',
                    'message' => 'The webhook could not be verified.',
                ],
            ], 401);
        }

        ProcessWebhookEvent::dispatch(
            (string) ($payload['provider'] ?? 'manual'),
            (string) ($payload['event_id'] ?? ''),
            (string) ($payload['event_type'] ?? ''),
            $payload,
        );

        return response()->json(['accepted' => true], 202);
    }
}
