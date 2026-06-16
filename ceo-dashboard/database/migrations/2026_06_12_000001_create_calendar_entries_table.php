<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_entries', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('address');
            $table->string('phone')->nullable();

            // Target installation: single date (start_date) or a range
            // (start_date → end_date / estimated completion).
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_birthday')->default(false);
            $table->date('dob')->nullable();

            $table->text('order_details')->nullable();

            $table->string('status')->default('scheduled'); // scheduled|in_progress|completed|cancelled
            $table->string('business')->nullable();          // bcf|bgr|rg|other
            $table->string('assigned_to')->nullable();       // installer / staff
            $table->unsignedTinyInteger('reminder_days')->default(3);

            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('start_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_entries');
    }
};
