<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAsset extends Model
{
    protected $guarded = [];

    protected $casts = [
        'domain_expiry' => 'date',
        'ssl_expiry' => 'date',
        'monthly_cost' => 'decimal:2',
    ];

    public function lead()
    {
        return $this->belongsTo(Employee::class, 'assigned_lead_id');
    }

    public function features()
    {
        return $this->hasMany(AssetFeature::class);
    }
}
