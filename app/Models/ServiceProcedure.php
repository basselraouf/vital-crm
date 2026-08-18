<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
