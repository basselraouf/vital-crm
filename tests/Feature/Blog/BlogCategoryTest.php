<?php

namespace Tests\Feature\Blog;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BlogCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    private function auth(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ── Public index ───────────────────────────────────────────────────────

    public function test_public_index_lists_categories(): void
    {
        BlogCategory::factory()->count(3)->create();

        $this->getJson('/api/website/blog-categories')
             ->assertOk()
             ->assertJsonPath('status', 1);
    }

    // ── Dashboard index ────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->getJson('/api/blog-categories')->assertUnauthorized();
    }

    public function test_index_returns_categories_with_blog_count(): void
    {
        $cat = BlogCategory::factory()->create();
        Blog::factory()->published()->count(2)->create(['category_id' => $cat->id]);

        $response = $this->getJson('/api/blog-categories', $this->auth());

        $response->assertOk();
        $data = collect($response->json('data'))->firstWhere('id', $cat->id);
        $this->assertEquals(2, $data['blogs_count']);
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function test_store_creates_category(): void
    {
        $this->postJson('/api/blog-categories', [
            'name' => 'New Category',
        ], $this->auth())
             ->assertCreated()
             ->assertJsonPath('data.name', 'New Category');

        $this->assertDatabaseHas('blog_categories', ['name' => 'New Category']);
    }

    public function test_store_auto_generates_slug(): void
    {
        $this->postJson('/api/blog-categories', [
            'name' => 'Auto Slug Test',
        ], $this->auth())->assertCreated();

        $this->assertDatabaseHas('blog_categories', ['slug' => 'auto-slug-test']);
    }

    public function test_store_validates_required_name(): void
    {
        $this->postJson('/api/blog-categories', [], $this->auth())
             ->assertStatus(422);
    }

    public function test_store_validates_unique_slug(): void
    {
        BlogCategory::factory()->create(['slug' => 'existing-cat-slug']);

        $this->postJson('/api/blog-categories', [
            'name' => 'Something',
            'slug' => 'existing-cat-slug',
        ], $this->auth())->assertStatus(422);
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function test_show_returns_category(): void
    {
        $cat = BlogCategory::factory()->create();

        $this->getJson("/api/blog-categories/{$cat->id}", $this->auth())
             ->assertOk()
             ->assertJsonPath('data.id', $cat->id);
    }

    public function test_show_returns_404_for_nonexistent(): void
    {
        $this->getJson('/api/blog-categories/99999', $this->auth())->assertNotFound();
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function test_update_modifies_category(): void
    {
        $cat = BlogCategory::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/blog-categories/{$cat->id}", [
            'name' => 'New Name',
        ], $this->auth())
             ->assertOk()
             ->assertJsonPath('data.name', 'New Name');
    }

    public function test_update_prevents_self_referencing_parent(): void
    {
        $cat = BlogCategory::factory()->create();

        $this->putJson("/api/blog-categories/{$cat->id}", [
            'parent_id' => $cat->id,
        ], $this->auth())->assertStatus(422);
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_destroy_deletes_empty_category(): void
    {
        $cat = BlogCategory::factory()->create();

        $this->deleteJson("/api/blog-categories/{$cat->id}", [], $this->auth())->assertOk();

        $this->assertDatabaseMissing('blog_categories', ['id' => $cat->id]);
    }

    public function test_destroy_refuses_category_with_blogs(): void
    {
        $cat = BlogCategory::factory()->create();
        Blog::factory()->create(['category_id' => $cat->id]);

        $this->deleteJson("/api/blog-categories/{$cat->id}", [], $this->auth())
             ->assertStatus(422);
    }
}
