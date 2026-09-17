<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('station')->nullable();
            // Contexto libre que se inyecta al clasificador: conductores, secciones, tema del día.
            $table->text('context')->nullable();
            $table->timestamps();
        });

        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['program_id', 'started_at']);
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 32)->unique();
            $table->string('name')->nullable();          // pushName de WhatsApp
            $table->string('display_name')->nullable();  // editable por el productor
            $table->string('location')->nullable();      // última localidad inferida
            $table->unsignedInteger('messages_count')->default(0);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->unsignedInteger('messages_count')->default(0);
            $table->timestamp('first_at');
            $table->timestamp('last_at');
            $table->timestamps();
            $table->index(['program_id', 'last_at']);
        });
        DB::statement('ALTER TABLE topics ADD COLUMN centroid vector(1536)');

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 16);              // evolution | meta
            $table->string('wa_message_id', 128)->unique();
            $table->string('type', 16);                  // text | audio | image | video | document | sticker | other
            $table->text('body')->nullable();            // texto o caption
            $table->string('media_path')->nullable();
            $table->string('media_mime', 64)->nullable();
            $table->unsignedSmallInteger('media_duration_s')->nullable();
            $table->text('transcript')->nullable();
            $table->string('transcript_provider', 16)->nullable();
            $table->unsignedInteger('transcript_ms')->nullable();
            // received → media → transcribing → classifying → ready | failed
            $table->string('status', 16)->default('received');
            $table->text('error')->nullable();
            $table->string('intent', 24)->nullable();
            $table->string('sentiment', 12)->nullable();
            $table->string('topic_label')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('on_air_score')->nullable();
            $table->jsonb('moderation')->nullable();
            $table->jsonb('classification')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->index(['program_id', 'sent_at']);
            $table->index(['program_id', 'status']);
            $table->index('contact_id');
        });
        DB::statement('ALTER TABLE messages ADD COLUMN embedding vector(1536)');

        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 12)->default('pending'); // pending | on_air | done | discarded
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique('message_id');
            $table->index(['program_id', 'status', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('highlights');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('shows');
        Schema::dropIfExists('programs');
    }
};
