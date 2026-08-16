<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogsSeeder extends Seeder
{
    /**
     * Migrate blogs data from the old WordPress database (word_press)
     * into the new vital-crm blog_categories and blogs tables.
     *
     * WordPress source tables used:
     *   - wp_terms           → blog_categories (name, slug)
     *   - wp_term_taxonomy   → blog_categories (description, parent)
     *   - wp_posts           → blogs (title, slug, content, excerpt, status, dates)
     *   - wp_postmeta        → blogs (thumbnail URL, SEO fields, views)
     *   - wp_term_relationships + wp_term_taxonomy → blogs.category_id
     */
    public function run(): void
    {
        $wp = DB::connection('wordpress');

        // ── 1. Seed blog_categories from WordPress categories ─────────────
        $this->command->info('Seeding blog_categories...');

        $wpCategories = $wp->table('wp_terms as t')
            ->join('wp_term_taxonomy as tt', 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'category')
            ->select(
                't.term_id as wp_id',
                't.name',
                't.slug',
                'tt.description',
                'tt.parent',
                'tt.count'
            )
            ->get();

        // Map old WP term_id → new category id
        $categoryIdMap = [];

        foreach ($wpCategories as $cat) {
            $newId = DB::table('blog_categories')->insertGetId([
                'name'        => html_entity_decode($cat->name, ENT_QUOTES, 'UTF-8'),
                'slug'        => $cat->slug,
                'description' => $cat->description ?: null,
                'parent_id'   => null, // will update after all inserted
                'sort_order'  => 0,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $categoryIdMap[$cat->wp_id] = [
                'new_id'    => $newId,
                'wp_parent' => $cat->parent,
            ];
        }

        // Update parent_id references
        foreach ($categoryIdMap as $wpId => $data) {
            if ($data['wp_parent'] && isset($categoryIdMap[$data['wp_parent']])) {
                DB::table('blog_categories')
                    ->where('id', $data['new_id'])
                    ->update(['parent_id' => $categoryIdMap[$data['wp_parent']]['new_id']]);
            }
        }

        $this->command->info('blog_categories seeded: ' . count($categoryIdMap) . ' records.');

        // ── 2. Seed blogs from WordPress posts ────────────────────────────
        $this->command->info('Seeding blogs from wp_posts...');

        $wpPosts = $wp->table('wp_posts')
            ->where('post_type', 'post')
            ->where('post_status', 'publish')
            ->select(
                'ID', 'post_title', 'post_name', 'post_content',
                'post_excerpt', 'post_status', 'post_date', 'post_modified',
                'comment_count'
            )
            ->get();

        $this->command->info('Found ' . $wpPosts->count() . ' published posts.');

        foreach ($wpPosts as $post) {

            // ── Get all meta for this post in one query ──────────────────
            $metaRows = $wp->table('wp_postmeta')
                ->where('post_id', $post->ID)
                ->whereIn('meta_key', [
                    '_thumbnail_id',
                    'rank_math_title',
                    'rank_math_description',
                    'rank_math_focus_keyword',
                    'tie_views',
                ])
                ->pluck('meta_value', 'meta_key');

            // ── Resolve thumbnail URL ────────────────────────────────────
            $featuredImage = null;
            if (!empty($metaRows['_thumbnail_id'])) {
                $attachment = $wp->table('wp_posts')
                    ->where('ID', $metaRows['_thumbnail_id'])
                    ->value('guid');
                $featuredImage = $attachment ?: null;
            }

            // ── Get category for this post ───────────────────────────────
            $wpCategoryId = $wp->table('wp_term_relationships as tr')
                ->join('wp_term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->where('tr.object_id', $post->ID)
                ->where('tt.taxonomy', 'category')
                ->value('tt.term_id');

            $newCategoryId = ($wpCategoryId && isset($categoryIdMap[$wpCategoryId]))
                ? $categoryIdMap[$wpCategoryId]['new_id']
                : null;

            // ── Insert blog ──────────────────────────────────────────────
            DB::table('blogs')->insert([
                'title'            => html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8'),
                'slug'             => $post->post_name,
                'content'          => $post->post_content,
                'excerpt'          => $post->post_excerpt ?: null,
                'featured_image'   => $featuredImage,
                'category_id'      => $newCategoryId,
                'meta_title'       => $metaRows['rank_math_title'] ?? null,
                'meta_description' => $metaRows['rank_math_description'] ?? null,
                'focus_keyword'    => $metaRows['rank_math_focus_keyword'] ?? null,
                'status'           => 'published',
                'published_at'     => $post->post_date,
                'views_count'      => (int) ($metaRows['tie_views'] ?? 0),
                'author_id'        => null,
                'created_at'       => $post->post_date,
                'updated_at'       => $post->post_modified,
            ]);
        }

        $this->command->info('Blogs seeded successfully: ' . $wpPosts->count() . ' records.');
    }
}
