<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('color', 9)->default('#4E7C59');
            $table->timestamps();
        });

        Schema::create('card_label', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->unique(['card_id', 'label_id']);
        });

        Schema::create('card_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['card_id', 'user_id']);
        });

        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Checklist');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->cascadeOnDelete();
            $table->string('content');
            $table->boolean('is_done')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('card_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('card_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('path');           // storage path on the 'boards' disk
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_attachments');
        Schema::dropIfExists('card_activities');
        Schema::dropIfExists('card_comments');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('card_member');
        Schema::dropIfExists('card_label');
        Schema::dropIfExists('labels');
    }
};
