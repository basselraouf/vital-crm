<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AccommodationImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    protected $hidden = ['image'];
    // ── Relations ────────────────────────────────────────────────────────────

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset(Storage::url($this->image));
    }
}
