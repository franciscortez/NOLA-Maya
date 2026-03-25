<?php

namespace App\Http\Controllers;

use App\Models\LocationToken;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProviderConfigController extends Controller
{
    /**
     * Display the configuration page.
     */
    public function show(Request $request): View
    {
        $locationId = $request->query('location_id');
        $location = LocationToken::where('location_id', $locationId)->firstOrFail();

        return view('config.config', compact('location'));
    }

    /**
     * Save the Maya configuration keys.
     */
    public function save(Request $request): RedirectResponse
    {
        $request->validate([
            'location_id' => 'required|exists:location_tokens,location_id',
            'maya_test_public_key' => 'nullable|string',
            'maya_test_secret_key' => 'nullable|string',
            'maya_live_public_key' => 'nullable|string',
            'maya_live_secret_key' => 'nullable|string',
        ]);

        $location = LocationToken::where('location_id', $request->location_id)->firstOrFail();

        Log::info('Config: Updating Maya credentials', ['location_id' => $location->location_id]);

        $location->update([
            'maya_test_public_key' => $request->maya_test_public_key,
            'maya_test_secret_key' => $request->maya_test_secret_key,
            'maya_live_public_key' => $request->maya_live_public_key,
            'maya_live_secret_key' => $request->maya_live_secret_key,
        ]);

        return back()->with('success', 'Configuration updated successfully!');
    }
}
