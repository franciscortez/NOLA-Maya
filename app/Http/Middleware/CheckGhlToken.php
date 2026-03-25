<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LocationToken;
use App\Services\GhlOauthService;
use Illuminate\Support\Facades\Log;

class CheckGhlToken
{
    protected GhlOauthService $ghlService;

    public function __construct(GhlOauthService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locationId = $request->input('location_id') ?? $request->input('locationId');

        if ($locationId) {
            $token = LocationToken::where('location_id', $locationId)->first();

            if ($token && $token->expires_at) {
                // If token is expired or expiring within 5 minutes
                if (now()->addMinutes(5)->gt($token->expires_at)) {
                    Log::info('Middleware: Preemptive token refresh triggered', ['location_id' => $locationId]);
                    $this->ghlService->refreshToken($token);
                }
            } else if (!$token) {
                Log::warning('Middleware: No token found for locationId provided', ['location_id' => $locationId]);
            }
        }

        return $next($request);
    }
}
