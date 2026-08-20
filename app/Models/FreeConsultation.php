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
}
