<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The single agency (Company) OAuth connection.
        Schema::create('ghl_oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('scope')->nullable();
            $table->timestamps();
        });

        // Sub-accounts (locations) discovered under the agency.
        Schema::create('ghl_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_id')->unique();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true); // shown in the CEO view
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghl_locations');
        Schema::dropIfExists('ghl_oauth_tokens');
    }
};
