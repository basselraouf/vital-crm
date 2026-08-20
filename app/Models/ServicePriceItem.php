<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePriceItem extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'price',
        'note',
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
