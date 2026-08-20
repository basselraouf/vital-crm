<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('packages_tagline')->nullable()->after('why_us_points');         // e.g. "ALL-INCLUSIVE"
            $table->string('packages_description')->nullable()->after('packages_tagline');   // subtitle under section title
            $table->json('packages_include')->nullable()->after('packages_description');     // "Packages Include" bullet list
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['packages_tagline', 'packages_description', 'packages_include']);
        });
    }
};
