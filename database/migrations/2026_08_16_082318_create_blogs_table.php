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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // ── Core Content (from wp_posts) ──────────────────────────────
            $table->string('title');                         // post_title
            $table->string('slug')->unique();                // post_name (URL-friendly)
            $table->longText('content');                     // post_content (full HTML body)
            $table->text('excerpt')->nullable();             // post_excerpt (short summary)

            // ── Featured Image ─────────────────────────────────────────────
            $table->string('featured_image')->nullable();    // full URL from wp attachment guid

            // ── Category ──────────────────────────────────────────────────
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                  ->references('id')
                  ->on('blog_categories')
                  ->onDelete('set null');

            // ── SEO Fields (from Rank Math / Yoast wp_postmeta) ───────────
            $table->string('meta_title')->nullable();        // rank_math_title
            $table->text('meta_description')->nullable();    // rank_math_description
            $table->string('focus_keyword')->nullable();     // rank_math_focus_keyword

            // ── Publishing ────────────────────────────────────────────────
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();   // post_date (when status = publish)

            // ── Stats ─────────────────────────────────────────────────────
            $table->unsignedBigInteger('views_count')->default(0); // tie_views

            // ── Author ────────────────────────────────────────────────────
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────
            // Single-column: for simple WHERE filters
            $table->index('status');
            $table->index('published_at');
            $table->index('category_id');
            $table->index('author_id');
            $table->index('views_count');           // for ORDER BY views_count DESC

            // Composite: most common query on website (published blogs by date)
            $table->index(['status', 'published_at'], 'blogs_status_published_at_idx');

            // Composite: dashboard filter by status + category
            $table->index(['status', 'category_id'], 'blogs_status_category_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
