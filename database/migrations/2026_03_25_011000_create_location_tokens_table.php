<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('location_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('location_id')->unique();
            $table->string('location_name');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at')->nullable();
            
            // Maya Credentials (Per Location)
            $table->text('maya_live_public_key')->nullable();
            $table->text('maya_live_secret_key')->nullable();
            $table->text('maya_test_public_key')->nullable();
            $table->text('maya_test_secret_key')->nullable();
            
            // Maya Webhooks (Per Location)
            $table->string('live_webhook_id')->nullable();
            $table->string('test_webhook_id')->nullable();
            $table->text('live_webhook_secret')->nullable();
            $table->text('test_webhook_secret')->nullable();
            
            $table->string('user_type')->default('Location');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_tokens');
    }
};
