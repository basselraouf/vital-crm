<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('free_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            
            // Basic Info
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->date('preferred_date')->nullable();
            
            // Medical Info (Nullable as requested)
            $table->integer('age')->nullable();
            $table->string('weight')->nullable(); // String to allow formats like "80 kg" or "180 lbs"
            $table->text('previous_surgeries')->nullable();
            
            // Extra form fields
            $table->string('how_did_you_hear')->nullable();
            $table->text('additional_notes')->nullable();
            
            // CRM tracking
            $table->enum('status', [
                'new', 
                'contacted', 
                'scheduled', 
                'completed', 
                'cancelled', 
                'no_show', 
                'unqualified'
            ])->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_consultations');
    }
};
