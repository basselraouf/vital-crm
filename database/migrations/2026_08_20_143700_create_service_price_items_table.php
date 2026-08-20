<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_price_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('name');                          // e.g. "Classic Gastric Sleeve"
            $table->string('price')->nullable();             // e.g. "From £2,800 to £3,200" or "£3,500" — flexible string
            $table->string('note')->nullable();              // e.g. "no scar, no pain"
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_price_items');
    }
};
