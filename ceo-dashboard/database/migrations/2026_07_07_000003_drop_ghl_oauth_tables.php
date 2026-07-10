<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the abandoned GoHighLevel agency-OAuth tables.
 *
 * OAuth was blocked by a GoHighLevel marketplace SSO bug and superseded by
 * per-sub-account Private Integration Tokens (see the `ghl_accounts` table).
 * dropIfExists keeps this a no-op on fresh installs where the tables were
 * never created.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ghl_oauth_tokens');
        Schema::dropIfExists('ghl_locations');
    }

    public function down(): void
    {
        // One-way cleanup: the OAuth feature is gone, so there is nothing to
        // restore. Intentionally left empty.
    }
};
