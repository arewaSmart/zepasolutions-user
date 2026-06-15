<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'referenceId',
        'transaction_ref',
        'service_type',
        'status',
        'type',
        'gateway',
        'service_description',
        'description',
        'payerid',
        'payer_name',
        'payer_email',
        'payer_phone',
        'performed_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Define the inverse relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for service_type with fallback to metadata service type or transaction type
    public function getServiceTypeAttribute()
    {
        if (!empty($this->attributes['service_type'])) {
            return $this->attributes['service_type'];
        }

        $meta = $this->metadata;
        if (!empty($meta['service'])) {
            return ucfirst($meta['service']);
        }

        if (!empty($this->attributes['type'])) {
            return ucfirst($this->attributes['type']);
        }

        return null;
    }

    // Accessor for service_description with fallback to description
    public function getServiceDescriptionAttribute()
    {
        return $this->attributes['service_description'] ?? ($this->attributes['description'] ?? null);
    }

    // Accessor for referenceId with fallback to transaction_ref
    public function getReferenceIdAttribute()
    {
        return $this->attributes['referenceId'] ?? ($this->attributes['transaction_ref'] ?? null);
    }
}
