<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('rating', 2, 1)->default(0);            // e.g. 4.9
            $table->string('distance_text')->nullable();             // e.g. "6 min walk to Medical Center"
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('max_guests')->default(2);
            $table->unsignedSmallInteger('area_sqm')->nullable();    // e.g. 54
            $table->decimal('price_per_night', 8, 2);                // e.g. 145.00
            $table->string('currency', 10)->default('USD');
            $table->json('amenities')->nullable();                   // ["Free Wi-Fi","Kitchenette",...]
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
