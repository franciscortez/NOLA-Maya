<?php

namespace App\Services;

use App\Models\LocationToken;
use Illuminate\Support\Facades\Log;

class ProviderConfigService
{
    /**
     * Update Maya configuration for a location.
     */
    public function updateMayaConfig(array $data): LocationToken
    {
        $location = LocationToken::where('location_id', $data['location_id'])->firstOrFail();

        Log::info('ProviderConfigService: Updating Maya credentials', [
            'location_id' => $location->location_id,
            'has_test_public' => !empty($data['maya_test_public_key']),
            'has_test_secret' => !empty($data['maya_test_secret_key']),
            'has_live_public' => !empty($data['maya_live_public_key']),
            'has_live_secret' => !empty($data['maya_live_secret_key']),
        ]);

        $location->update([
            'maya_test_public_key' => $data['maya_test_public_key'] ?? null,
            'maya_test_secret_key' => $data['maya_test_secret_key'] ?? null,
            'maya_live_public_key' => $data['maya_live_public_key'] ?? null,
            'maya_live_secret_key' => $data['maya_live_secret_key'] ?? null,
        ]);

        return $location;
    }
}
