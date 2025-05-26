<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalTokenService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $cacheKey = 'paypal_access_token';
    
    public function __construct()
    {
        $this->baseUrl = config('paypal.mode') === 'sandbox' 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
        $this->clientId = config('paypal.client_id');
        $this->clientSecret = config('paypal.client_secret');
    }
    
    /**
     * Get a valid access token
     */
    public function getAccessToken(): string
    {
        // Try to get token from cache first
        $token = Cache::get($this->cacheKey);
        
        if ($token) {
            return $token;
        }
        
        return $this->refreshAccessToken();
    }
    
    /**
     * Force refresh the access token
     */
    public function refreshAccessToken(): string
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials'
                ]);
            
            if (!$response->successful()) {
                Log::error('PayPal token refresh failed', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                throw new Exception('Failed to refresh PayPal access token');
            }
            
            $data = $response->json();
            $token = $data['access_token'];
            $expiresIn = $data['expires_in'];
            
            // Cache the token for slightly less than its expiry time
            // to ensure we never use an expired token
            Cache::put($this->cacheKey, $token, now()->addSeconds($expiresIn - 60));
            
            return $token;
        } catch (Exception $e) {
            Log::error('PayPal token refresh error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Clear the cached token
     */
    public function clearToken(): void
    {
        Cache::forget($this->cacheKey);
    }
    
    /**
     * Check if we have a valid cached token
     */
    public function hasValidToken(): bool
    {
        return Cache::has($this->cacheKey);
    }
    
    /**
     * Get token expiration time
     */
    public function getTokenExpiration(): ?\Carbon\Carbon
    {
        return Cache::get("{$this->cacheKey}:expires");
    }
    
    /**
     * Get authorization header with bearer token
     */
    public function getAuthorizationHeader(): string
    {
        return 'Bearer ' . $this->getAccessToken();
    }
}
