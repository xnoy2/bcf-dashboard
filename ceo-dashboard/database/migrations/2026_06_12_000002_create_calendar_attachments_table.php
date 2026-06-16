<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_entry_id')->constrained()->cascadeOnDelete();
            $table->string('path');           // storage path on the 'calendar' disk
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_attachments');
    }
};
