<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();              // e.g. "TRANSFORM YOUR HEALTH WITH EXPERT BARIATRIC SURGERY"
            $table->text('short_description')->nullable();      // paragraph under tagline
            $table->string('image')->nullable();                // featured image path or URL
            $table->json('benefits')->nullable();               // service-level benefits list (right-side box)
            $table->json('why_us_points')->nullable();          // "Why Choose Vital Global Care?" bullets
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

