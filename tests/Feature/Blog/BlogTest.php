<?php

namespace Tests\Feature\Blog;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BlogTest extends TestCase
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

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PUBLIC WEBSITE ROUTES (no auth)
    // ═══════════════════════════════════════════════════════════════════════

    public function test_public_index_returns_published_blogs_only(): void
    {
        Blog::factory()->published()->count(3)->create();
        Blog::factory()->draft()->count(2)->create();

        $response = $this->getJson('/api/website/blogs');

        $response->assertOk()
                 ->assertJsonPath('status', 1)
                 ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_public_index_supports_search_filter(): void
    {
        Blog::factory()->published()->create(['title' => 'Bariatric Surgery Guide']);
        Blog::factory()->published()->create(['title' => 'Medical Tourism Egypt']);

        $response = $this->getJson('/api/website/blogs?search=bariatric');

        $response->assertOk()
                 ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_public_index_supports_category_filter(): void
    {
        $category = BlogCategory::factory()->create();
        Blog::factory()->published()->create(['category_id' => $category->id]);
        Blog::factory()->published()->create(['category_id' => null]);

        $response = $this->getJson("/api/website/blogs?category_id={$category->id}");

        $response->assertOk()
                 ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_public_index_supports_sort_by_views(): void
    {
        Blog::factory()->published()->create(['views_count' => 100]);
        Blog::factory()->published()->create(['views_count' => 500]);

        $response = $this->getJson('/api/website/blogs?sort_by=views_count&sort_dir=desc');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertGreaterThanOrEqual($data[1]['views_count'], $data[0]['views_count']);
    }

    public function test_get_blog_by_slug_returns_published_blog(): void
    {
        $blog = Blog::factory()->published()->create(['slug' => 'test-blog-slug']);

        $response = $this->getJson('/api/website/blogs/slug/test-blog-slug');

        $response->assertOk()
                 ->assertJsonPath('data.slug', 'test-blog-slug');
    }

    public function test_get_blog_by_slug_increments_views(): void
    {
        $blog = Blog::factory()->published()->create(['slug' => 'popular-blog', 'views_count' => 50]);

        $this->getJson('/api/website/blogs/slug/popular-blog')->assertOk();

        $this->assertEquals(51, $blog->fresh()->views_count);
    }

    public function test_get_blog_by_slug_returns_404_for_draft(): void
    {
        Blog::factory()->draft()->create(['slug' => 'hidden-draft']);

        $this->getJson('/api/website/blogs/slug/hidden-draft')->assertNotFound();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (auth required)
    // ═══════════════════════════════════════════════════════════════════════

    public function test_dashboard_index_requires_auth(): void
    {
        $this->getJson('/api/blogs')->assertUnauthorized();
    }

    public function test_dashboard_index_returns_all_statuses(): void
    {
        Blog::factory()->published()->count(2)->create();
        Blog::factory()->draft()->count(3)->create();

        $response = $this->getJson('/api/blogs', $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.pagination.total', 5);
    }

    public function test_dashboard_index_filters_by_status(): void
    {
        Blog::factory()->published()->count(2)->create();
        Blog::factory()->draft()->count(3)->create();

        $response = $this->getJson('/api/blogs?status=draft', $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_dashboard_index_filters_by_date_range(): void
    {
        Blog::factory()->published()->create(['published_at' => '2025-01-15']);
        Blog::factory()->published()->create(['published_at' => '2025-06-20']);
        Blog::factory()->published()->create(['published_at' => '2026-02-01']);

        $response = $this->getJson('/api/blogs?date_from=2025-01-01&date_to=2025-12-31', $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_dashboard_index_pagination(): void
    {
        Blog::factory()->count(20)->create();

        $response = $this->getJson('/api/blogs?per_page=5', $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.pagination.per_page', 5)
                 ->assertJsonPath('data.pagination.total', 20);
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function test_store_creates_blog_with_required_fields(): void
    {
        $response = $this->postJson('/api/blogs', [
            'title'   => 'New Medical Blog Post',
            'content' => 'This is the full content of the blog.',
            'status'  => 'draft',
        ], $this->authHeaders());

        $response->assertCreated()
                 ->assertJsonPath('data.title', 'New Medical Blog Post');

        $this->assertDatabaseHas('blogs', ['title' => 'New Medical Blog Post']);
    }

    public function test_store_auto_generates_slug(): void
    {
        $this->postJson('/api/blogs', [
            'title'   => 'Automatic Slug Generation',
            'content' => 'Content here.',
        ], $this->authHeaders())->assertCreated();

        $this->assertDatabaseHas('blogs', ['slug' => 'automatic-slug-generation']);
    }

    public function test_store_auto_sets_published_at_when_status_is_published(): void
    {
        $this->postJson('/api/blogs', [
            'title'   => 'Published Now',
            'content' => 'Content.',
            'status'  => 'published',
        ], $this->authHeaders())->assertCreated();

        $blog = Blog::where('title', 'Published Now')->first();
        $this->assertNotNull($blog->published_at);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->postJson('/api/blogs', [], $this->authHeaders())
             ->assertStatus(422);
    }

    public function test_store_validates_unique_slug(): void
    {
        Blog::factory()->create(['slug' => 'existing-slug']);

        $this->postJson('/api/blogs', [
            'title'   => 'Something',
            'content' => 'Content.',
            'slug'    => 'existing-slug',
        ], $this->authHeaders())->assertStatus(422);
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function test_show_returns_blog_by_id(): void
    {
        $blog = Blog::factory()->create();

        $response = $this->getJson("/api/blogs/{$blog->id}", $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.id', $blog->id)
                 ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'content', 'status']]);
    }

    public function test_show_returns_404_for_nonexistent_blog(): void
    {
        $this->getJson('/api/blogs/99999', $this->authHeaders())->assertNotFound();
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function test_update_modifies_blog_fields(): void
    {
        $blog = Blog::factory()->create(['title' => 'Old Title']);

        $response = $this->postJson("/api/blogs/{$blog->id}", [
            'title' => 'Updated Title',
        ], $this->authHeaders());

        $response->assertOk()
                 ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('blogs', ['id' => $blog->id, 'title' => 'Updated Title']);
    }

    public function test_update_returns_404_for_nonexistent_blog(): void
    {
        $this->postJson('/api/blogs/99999', ['title' => 'X'], $this->authHeaders())->assertNotFound();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_destroy_deletes_blog(): void
    {
        $blog = Blog::factory()->create();

        $this->deleteJson("/api/blogs/{$blog->id}", [], $this->authHeaders())->assertOk();

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_blog(): void
    {
        $this->deleteJson('/api/blogs/99999', [], $this->authHeaders())->assertNotFound();
    }
}
