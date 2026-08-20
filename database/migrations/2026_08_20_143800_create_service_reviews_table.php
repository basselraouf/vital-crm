<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('reviewer_name')->nullable();     // optional — admin can leave blank
            $table->string('reviewer_location')->nullable(); // e.g. "UK", "Egypt"
            $table->unsignedTinyInteger('rating');           // 1–5 stars
            $table->text('content');                         // the review text
            $table->string('media_path')->nullable();        // for admin uploads (image or video)
            $table->string('media_type')->nullable();        // 'image' or 'video'
            $table->enum('source', ['website', 'admin'])->default('website');
            $table->enum('status', ['pending', 'rejected', 'selected', 'drafted'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reviews');
    }
};
