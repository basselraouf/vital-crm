<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'full_name',
        'email',
        'phone',
        'preferred_date',
        'age',
        'weight',
        'previous_surgeries',
        'how_did_you_hear',
        'additional_notes',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class, 'service_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, string $term): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%")
                ->orWhere('phone', 'LIKE', "%{$term}%")
                ->orWhere('how_did_you_hear', 'LIKE', "%{$term}%");
        });
    }

    public function scopeSorted(\Illuminate\Database\Eloquent\Builder $query, string $sortBy = 'created_at', string $dir = 'desc'): \Illuminate\Database\Eloquent\Builder
    {
        $allowed = ['full_name', 'created_at', 'preferred_date', 'status'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
        $dir     = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        return $query->orderBy($sortBy, $dir);
    }
}
