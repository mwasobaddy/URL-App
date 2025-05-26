<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalAPIService
{
    protected PayPalTokenService $tokenService;
    protected string $baseUrl;
    protected int $maxRetries = 3;
    protected array $retryableStatusCodes = [429, 500, 502, 503, 504];
    
    public function __construct(PayPalTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->baseUrl = config('paypal.mode') === 'sandbox' 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
    }
    
    /**
     * Make a request to PayPal API with retry mechanism
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $attempt = 1;
        $lastException = null;
        
        while ($attempt <= $this->maxRetries) {
            try {
                $response = Http::withHeaders(array_merge([
                    'Authorization' => $this->tokenService->getAuthorizationHeader(),
                    'PayPal-Request-Id' => $this->generateIdempotencyKey($method, $endpoint, $data),
                ], $headers))
                    ->withoutVerifying() // For development only, remove in production
                    ->$method("{$this->baseUrl}{$endpoint}", $data);
                
                // Handle rate limiting
                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After', 5);
                    sleep((int)$retryAfter);
                    $attempt++;
                    continue;
                }
                
                // Handle token expiration
                if ($response->status() === 401) {
                    $this->tokenService->refreshAccessToken();
                    $attempt++;
                    continue;
                }
                
                // Handle successful response
                if ($response->successful()) {
                    return $response->json();
                }
                
                // Handle other errors
                if (!in_array($response->status(), $this->retryableStatusCodes)) {
                    $this->handleError($response);
                }
                
                $attempt++;
                sleep(pow(2, $attempt - 1)); // Exponential backoff
                
            } catch (Exception $e) {
                $lastException = $e;
                Log::error('PayPal API error', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'endpoint' => $endpoint
                ]);
                
                if ($attempt === $this->maxRetries) {
                    throw $lastException;
                }
                
                $attempt++;
                sleep(pow(2, $attempt - 1));
            }
        }
        
        throw $lastException ?? new Exception('Failed to make PayPal API request after multiple attempts');
    }
    
    /**
     * Generate idempotency key for requests
     */
    protected function generateIdempotencyKey(string $method, string $endpoint, array $data): string
    {
        return md5($method . $endpoint . json_encode($data));
    }
    
    /**
     * Handle error responses from PayPal
     */
    protected function handleError($response): void
    {
        $error = $response->json();
        Log::error('PayPal API error response', [
            'status' => $response->status(),
            'error' => $error
        ]);
        
        throw new Exception(
            $error['message'] ?? 'Unknown PayPal API error',
            $response->status()
        );
    }
    
    /**
     * Create a PayPal product
     */
    public function createProduct(Plan $plan): array
    {
        return $this->makeRequest('post', '/v1/catalogs/products', [
            'name' => $plan->name,
            'description' => $plan->description,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);
    }
    
    /**
     * Create a PayPal billing plan
     */
    public function createBillingPlan(Plan $plan, string $productId, string $interval = 'MONTH'): array
    {
        $data = [
            'product_id' => $productId,
            'name' => "{$plan->name} - " . ($interval === 'MONTH' ? 'Monthly' : 'Yearly'),
            'description' => $plan->description,
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => $interval,
                        'interval_count' => 1
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => $interval === 'MONTH' ? $plan->monthly_price : $plan->yearly_price,
                            'currency_code' => config('paypal.currency')
                        ]
                    ]
                ]
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => config('paypal.currency')
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3
            ]
        ];
        
        return $this->makeRequest('post', '/v1/billing/plans', $data);
    }
    
    /**
     * Get a PayPal subscription details
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->makeRequest('get', "/v1/billing/subscriptions/{$subscriptionId}");
    }
    
    /**
     * Cancel a PayPal subscription
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'Customer requested cancellation'): array
    {
        return $this->makeRequest('post', "/v1/billing/subscriptions/{$subscriptionId}/cancel", [
            'reason' => $reason
        ]);
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(array $headers, string $body): bool
    {
        $data = [
            'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
            'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
            'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
            'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
            'webhook_id' => config('paypal.webhook_id'),
            'webhook_event' => json_decode($body, true)
        ];
        
        try {
            $response = $this->makeRequest('post', '/v1/notifications/verify-webhook-signature', $data);
            return ($response['verification_status'] ?? '') === 'SUCCESS';
        } catch (Exception $e) {
            Log::error('Webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
