<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rating',
        'distance_text',
        'bedrooms',
        'max_guests',
        'area_sqm',
        'price_per_night',
        'currency',
        'amenities',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'amenities'       => 'array',
        'rating'          => 'decimal:1',
        'price_per_night' => 'decimal:2',
        'bedrooms'        => 'integer',
        'max_guests'      => 'integer',
        'area_sqm'        => 'integer',
        'sort_order'      => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function images()
    {
        return $this->hasMany(AccommodationImage::class)->orderBy('sort_order');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%")
                ->orWhere('distance_text', 'LIKE', "%{$term}%");
        });
    }

    public function scopeSorted(Builder $query, string $sortBy = 'sort_order', string $dir = 'asc'): Builder
    {
        $allowed = ['sort_order', 'name', 'price_per_night', 'rating', 'created_at'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'sort_order';
        $dir     = strtolower($dir) === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sortBy, $dir);
    }
}
