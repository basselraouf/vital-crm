<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'short_description',
        'image',
        'benefits',
        'why_us_points',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'benefits'      => 'array',
        'why_us_points' => 'array',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    protected $hidden = [
        'image',
    ];

    // ── Accessor ──────────────────────────────────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset(Storage::url($this->image));
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function procedures()
    {
        return $this->hasMany(ServiceProcedure::class)->orderBy('sort_order');
    }

    public function packages()
    {
        return $this->hasMany(ServicePackage::class)->orderBy('sort_order');
    }

    public function consultations()
    {
        return $this->hasMany(FreeConsultation::class, 'service_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('tagline', 'LIKE', "%{$term}%")
                ->orWhere('short_description', 'LIKE', "%{$term}%");
        });
    }

    public function scopeSorted(Builder $query, string $sortBy = 'sort_order', string $dir = 'asc'): Builder
    {
        $allowed = ['sort_order', 'name', 'created_at'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'sort_order';
        $dir     = strtolower($dir) === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sortBy, $dir);
    }
}
