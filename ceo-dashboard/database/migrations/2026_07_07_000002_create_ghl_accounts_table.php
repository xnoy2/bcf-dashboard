<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-sub-account GoHighLevel connections (Private Integration tokens),
        // merged into config('integrations.accounts') at runtime.
        Schema::create('ghl_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('api_key'); // encrypted at rest
            $table->string('location_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghl_accounts');
    }
};
