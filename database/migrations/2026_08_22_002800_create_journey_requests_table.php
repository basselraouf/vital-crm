<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_requests', function (Blueprint $table) {
            $table->id();

            // ── Step 1: Patient & Medical Details ──────────────────────────
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('country_of_residence')->nullable();
            $table->string('procedure_sought')->nullable();     // e.g. "Orthopedic knee surgery"
            $table->text('medical_notes')->nullable();          // Existing conditions, medications, mobility needs...

            // ── Step 2: Travel & Security Clearance ────────────────────────
            $table->date('arrival_date')->nullable();
            $table->date('departure_date')->nullable();
            $table->string('passport_path')->nullable();        // uploaded file path
            $table->string('flight_ticket_path')->nullable();   // uploaded file path
            $table->boolean('fast_track_clearance')->default(false); // Priority Concierge flag

            // ── Step 3: Accommodation ──────────────────────────────────────
            $table->foreignId('accommodation_id')->nullable()->constrained('accommodations')->nullOnDelete();
            $table->unsignedSmallInteger('nights')->nullable(); // Length of stay

            // ── CRM Status Tracking ────────────────────────────────────────
            $table->enum('status', [
                'new',           // Just submitted — needs review
                'under_review',  // Team is reviewing the request
                'confirmed',     // Journey has been confirmed with the patient
                'in_progress',   // Patient is currently in Egypt / receiving treatment
                'completed',     // All done, patient returned home
                'cancelled',     // Patient or team cancelled
            ])->default('new');

            $table->text('internal_notes')->nullable(); // CRM agent internal notes, not visible to patient

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_requests');
    }
};
