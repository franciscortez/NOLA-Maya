<?php

namespace App\Services;

use App\Models\LocationToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GhlOauthService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->baseUrl = env('GHL_API_BASE', 'https://services.leadconnectorhq.com');
        $this->clientId = env('GHL_CLIENT_ID');
        $this->clientSecret = env('GHL_CLIENT_SECRET');
        $this->redirectUri = env('GHL_REDIRECT_URI');
    }

    /**
     * Get the Authorization URL for GHL login.
     */
    public function getAuthorizationUrl(bool $isWhiteLabel = false): string
    {
        $scopes = [
            'payments/orders.readonly',
            'payments/orders.write',
            'payments/subscriptions.readonly',
            'payments/transactions.readonly',
            'payments/orders.collectPayment',
            'payments/custom-provider.readonly',
            'payments/custom-provider.write',
            'products.readonly',
            'products/prices.readonly',
            'contacts.readonly',
            'invoices.readonly',
            'invoices.write',
            'locations.readonly'
        ];

        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
        ]);

        $baseUrl = $isWhiteLabel 
            ? 'https://marketplace.leadconnectorhq.com' 
            : 'https://marketplace.gohighlevel.com';

        return "{$baseUrl}/oauth/chooselocation?{$query}";
    }

    /**
     * Exchange the authorization code for an access token.
     */
    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        if ($response->failed()) {
            Log::error('GHL OAuth: Failed to exchange code for token', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new Exception('Failed to exchange code for token: ' . $response->body());
        }

        Log::info('GHL OAuth: Token exchange successful');
        return $response->json();
    }

    /**
     * Fetch the location details using the access token.
     */
    public function getLocationDetails(string $accessToken, string $locationId): array
    {
        $response = Http::withToken($accessToken)
            ->withHeaders(['Version' => env('GHL_API_VERSION', '2021-07-28')])
            ->get("{$this->baseUrl}/locations/{$locationId}");

        if ($response->failed()) {
            // Fallback if scopes or permissions fail quietly
            return ['name' => 'Unknown Location'];
        }

        return $response->json('location') ?? ['name' => 'Unknown Location'];
    }

    /**
     * Process the OAuth callback and save the token data.
     */
    public function processCallback(string $code): LocationToken
    {
        Log::info('GHL OAuth: Processing callback', ['code' => substr($code, 0, 10) . '...']);
        $tokenData = $this->exchangeCodeForToken($code);

        $locationId = $tokenData['locationId'] ?? null;
        if (!$locationId) {
            Log::error('GHL OAuth: Location ID missing from response');
            throw new Exception('Location ID missing from token response.');
        }

        // Try to fetch location name
        $locationDetails = $this->getLocationDetails($tokenData['access_token'], $locationId);
        $locationName = $locationDetails['name'] ?? $locationId;

        Log::info('GHL OAuth: Saving details for location', [
            'location_id' => $locationId,
            'name' => $locationName
        ]);

        return LocationToken::updateOrCreate(
            ['location_id' => $locationId],
            [
                'location_name' => $locationName,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => now()->addSeconds($tokenData['expires_in'] - 60), // Add 60s buffer
                'user_type' => $tokenData['userType'] ?? 'Location',
            ]
        );
    }

    /**
     * Refresh the expired access token.
     */
    public function refreshToken($token): ?LocationToken
    {
        $locationToken = is_string($token) 
            ? LocationToken::where('location_id', $token)->first() 
            : $token;

        if (!$locationToken || !$locationToken->refresh_token) {
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $locationToken->refresh_token,
            'user_type' => $locationToken->user_type,
            'redirect_uri' => $this->redirectUri,
        ]);

        if ($response->failed()) {
            Log::error('GHL OAuth: Token refresh failed', [
                'location_id' => $locationToken->location_id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return null;
        }

        $tokenData = $response->json();

        $locationToken->update([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? $locationToken->refresh_token,
            'expires_at' => now()->addSeconds($tokenData['expires_in'] - 60),
            'user_type' => $tokenData['userType'] ?? $locationToken->user_type,
        ]);

        Log::info('GHL OAuth: Token refreshed successfully', ['location_id' => $locationToken->location_id]);
        return $locationToken;
    }
}
