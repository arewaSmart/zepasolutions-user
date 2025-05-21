<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NIN_REQUEST extends Model
{
    use HasFactory;

    protected $table = 'nin_requests';

    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'trackingId',
        'nin_number',
        'service_type',
        'description',
        'uploads',
        'refunded_at',
    ];
}
