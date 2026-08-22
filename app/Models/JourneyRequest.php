<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class JourneyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country_of_residence',
        'procedure_sought',
        'medical_notes',
        'arrival_date',
        'departure_date',
        'passport_path',
        'flight_ticket_path',
        'fast_track_clearance',
        'accommodation_id',
        'nights',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'arrival_date'         => 'date',
        'departure_date'       => 'date',
        'fast_track_clearance' => 'boolean',
        'nights'               => 'integer',
    ];

    protected $appends = ['passport_url', 'flight_ticket_url'];

    protected $hidden = ['passport_path', 'flight_ticket_path'];
    // ── Relations ────────────────────────────────────────────────────────────

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id')
            ->select('id', 'name', 'slug', 'price_per_night', 'currency', 'amenities');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getPassportUrlAttribute(): ?string
    {
        if (!$this->passport_path) return null;
        if (str_starts_with($this->passport_path, 'http')) return $this->passport_path;
        return asset(Storage::url($this->passport_path));
    }

    public function getFlightTicketUrlAttribute(): ?string
    {
        if (!$this->flight_ticket_path) return null;
        if (str_starts_with($this->flight_ticket_path, 'http')) return $this->flight_ticket_path;
        return asset(Storage::url($this->flight_ticket_path));
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('full_name', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%")
                ->orWhere('phone', 'LIKE', "%{$term}%")
                ->orWhere('procedure_sought', 'LIKE', "%{$term}%")
                ->orWhere('country_of_residence', 'LIKE', "%{$term}%");
        });
    }

    public function scopeSorted(Builder $query, string $sortBy = 'created_at', string $dir = 'desc'): Builder
    {
        $allowed = ['full_name', 'created_at', 'arrival_date', 'departure_date', 'status', 'nights'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
        $dir     = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        return $query->orderBy($sortBy, $dir);
    }
}
