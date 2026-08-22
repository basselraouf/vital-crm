<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    protected $fillable = [
        'service_id',
        'reviewer_name',
        'reviewer_location',
        'rating',
        'content',
        'media_path',
        'media_type',
        'source',
        'status',
    ];

    protected $appends = [
        'media_url',
    ];

    public function getMediaUrlAttribute(): ?string
    {
        if (!$this->media_path) return null;
        if (str_starts_with($this->media_path, 'http')) return $this->media_path;
        return asset(\Illuminate\Support\Facades\Storage::url($this->media_path));
    }

    protected $casts = [
        'rating' => 'integer',
    ];

    public function service()
    {
        // service_id is nullable — generic reviews are not tied to a specific service
        return $this->belongsTo(Service::class)->withDefault();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSelected($query)
    {
        return $query->where('status', 'selected');
    }

    public function scopeFromWebsite($query)
    {
        return $query->where('source', 'website');
    }

    public function scopeFromAdmin($query)
    {
        return $query->where('source', 'admin');
    }
}
