<?php

namespace App\Http\Controllers;

use App\Http\Requests\GhlOauthRequest;
use App\Services\GhlOauthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GhlOauthController extends Controller
{
    protected GhlOauthService $ghlService;

    public function __construct(GhlOauthService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    /**
     * Redirect the user to the GoHighLevel OAuth page.
     * Supports ?whitelabel=1 for white-label marketplaces.
     */
    public function install(Request $request): RedirectResponse
    {
        $isWhiteLabel = $request->boolean('whitelabel');
        $url = $this->ghlService->getAuthorizationUrl($isWhiteLabel);
        return redirect()->away($url);
    }

    /**
     * Handle the OAuth callback from GoHighLevel.
     */
    public function callback(GhlOauthRequest $request): RedirectResponse
    {
        try {
            $locationToken = $this->ghlService->processCallback($request->code);

            return redirect()->route('config.show', ['location_id' => $locationToken->location_id])
                ->with('success', 'GoHighLevel successfully connected!');

        } catch (Exception $e) {
            Log::error('GHL OAuth Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('oauth.install')
                ->with('error', 'OAuth failed: ' . $e->getMessage());
        }
    }
}
