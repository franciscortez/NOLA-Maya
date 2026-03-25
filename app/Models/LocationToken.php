<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationToken extends Model
{
    protected $fillable = [
        'location_id',
        'location_name',
        'access_token',
        'refresh_token',
        'expires_at',
        'maya_live_public_key',
        'maya_live_secret_key',
        'maya_test_public_key',
        'maya_test_secret_key',
        'live_webhook_id',
        'test_webhook_id',
        'live_webhook_secret',
        'test_webhook_secret',
        'user_type',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'maya_live_secret_key' => 'encrypted',
        'maya_test_secret_key' => 'encrypted',
        'live_webhook_secret' => 'encrypted',
        'test_webhook_secret' => 'encrypted',
    ];
}
