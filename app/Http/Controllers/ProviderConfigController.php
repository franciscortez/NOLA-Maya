<?php

namespace App\Http\Controllers;

use App\Models\LocationToken;
use App\Http\Requests\ProviderConfigRequest;
use App\Services\ProviderConfigService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProviderConfigController extends Controller
{
    protected $service;

    public function __construct(ProviderConfigService $service)
    {
        $this->service = $service;
    }
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
    public function save(ProviderConfigRequest $request): RedirectResponse
    {
        Log::info('ConfigController: Received update request', ['location_id' => $request->location_id]);

        $this->service->updateMayaConfig($request->validated());

        return back()->with('success', 'Configuration updated successfully!');
    }
}
