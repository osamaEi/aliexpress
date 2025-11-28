<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AliExpressWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AliExpressWebhookController extends Controller
{
    protected $webhookService;

    public function __construct(AliExpressWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle AliExpress order status webhook
     * Supports both GET (verification) and POST (actual webhook) requests
     */
    public function handleOrderStatus(Request $request)
    {
        try {
            // If GET request, return success for AliExpress verification
            if ($request->isMethod('get')) {
                Log::info('AliExpress Webhook Verification Request', [
                    'ip' => $request->ip(),
                    'params' => $request->all(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'AliExpress webhook endpoint is active and ready to receive notifications',
                    'timestamp' => now()->toIso8601String(),
                ], 200);
            }

            // Log incoming webhook
            Log::info('AliExpress Webhook Received', [
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'ip' => $request->ip(),
            ]);

            // Verify webhook signature
            if (!$this->webhookService->verifySignature($request)) {
                Log::warning('AliExpress Webhook Signature Verification Failed', [
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            // Process the webhook
            $result = $this->webhookService->processWebhook($request->all());

            if ($result['success']) {
                Log::info('AliExpress Webhook Processed Successfully', [
                    'order_id' => $result['order_id'] ?? null,
                    'status' => $result['status'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ], 200);
            } else {
                Log::error('AliExpress Webhook Processing Failed', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'payload' => $request->all(),
                ]);

                // Return 200 to prevent webhook retries even on processing errors
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook received but processing failed',
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('AliExpress Webhook Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            // Return 200 to prevent webhook retries
            return response()->json([
                'success' => true,
                'message' => 'Webhook received',
            ], 200);
        }
    }

    /**
     * Handle AliExpress tracking update webhook
     */
    public function handleTrackingUpdate(Request $request)
    {
        try {
            Log::info('AliExpress Tracking Webhook Received', [
                'payload' => $request->all(),
            ]);

            // Verify webhook signature
            if (!$this->webhookService->verifySignature($request)) {
                Log::warning('AliExpress Tracking Webhook Signature Verification Failed');

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            // Process tracking update
            $result = $this->webhookService->processTrackingUpdate($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tracking update received',
            ], 200);

        } catch (\Exception $e) {
            Log::error('AliExpress Tracking Webhook Exception', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook received',
            ], 200);
        }
    }

    /**
     * Test endpoint to verify webhook configuration
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'AliExpress webhook endpoint is reachable',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
