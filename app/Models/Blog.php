<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'status',
        'published_at',
        'views_count',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views_count'  => 'integer',
        'category_id'  => 'integer',
        'author_id'    => 'integer',
    ];

    // ── Accessor ──────────────────────────────────────────────────────────
    // Handles both imported WordPress URLs (http...) and new local uploads

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) return null;
        if (str_starts_with($this->featured_image, 'http')) {
            return $this->featured_image;             // external URL (WordPress import)
        }
        return asset(Storage::url($this->featured_image)); // local storage path
    }

    // ── Relations ─────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    /** Only published + past publish date (for website) */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    /** Full-text-like search across title, excerpt, focus_keyword */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('excerpt', 'LIKE', "%{$term}%")
              ->orWhere('focus_keyword', 'LIKE', "%{$term}%");
        });
    }

    /** Whitelist-guarded sort — prevents SQL injection */
    public function scopeSorted(Builder $query, string $sortBy = 'published_at', string $dir = 'desc'): Builder
    {
        $allowed = ['published_at', 'views_count', 'title', 'created_at'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'published_at';
        $dir     = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $dir);
    }
}

