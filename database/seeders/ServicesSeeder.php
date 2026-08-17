<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    /**
     * Seed services from WordPress WooCommerce products.
     *
     * WordPress source tables:
     *   - wp_posts (post_type = 'product', post_status = 'publish') → services (name, slug, description)
     *   - wp_postmeta (_thumbnail_id) → services (image URL via wp_posts.guid)
     */
    public function run(): void
    {
        $wp = DB::connection('wordpress');

        $this->command->info('Seeding services from wp_posts (products)...');

        $wpProducts = $wp->table('wp_posts')
            ->where('post_type', 'product')
            ->where('post_status', 'publish')
            ->orderBy('menu_order')
            ->orderBy('ID')
            ->select('ID', 'post_title', 'post_name', 'post_excerpt', 'menu_order')
            ->get();

        $this->command->info("Found {$wpProducts->count()} service products.");

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($wpProducts as $index => $product) {
            // Resolve thumbnail URL
            $thumbnailId = $wp->table('wp_postmeta')
                ->where('post_id', $product->ID)
                ->where('meta_key', '_thumbnail_id')
                ->value('meta_value');

            $imageUrl = null;
            if ($thumbnailId) {
                $imageUrl = $wp->table('wp_posts')
                    ->where('ID', $thumbnailId)
                    ->value('guid');
            }

            // Use excerpt as description, fall back to null
            $description = !empty(trim($product->post_excerpt))
                ? html_entity_decode(trim($product->post_excerpt), ENT_QUOTES, 'UTF-8')
                : null;

            DB::table('services')->insert([
                'name'        => html_entity_decode($product->post_title, ENT_QUOTES, 'UTF-8'),
                'slug'        => $product->post_name ?: Str::slug($product->post_title),
                'description' => $description,
                'image'       => $imageUrl,
                'sort_order'  => $index + 1,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('Services seeded successfully: ' . DB::table('services')->count() . ' records.');
    }
}
