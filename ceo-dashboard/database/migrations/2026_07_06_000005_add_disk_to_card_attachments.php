<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_attachments', function (Blueprint $table) {
            // Which storage disk the file lives on ('boards' local, or 'r2').
            $table->string('disk', 20)->default('boards')->after('card_id');
        });
    }

    public function down(): void
    {
        Schema::table('card_attachments', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
};
