<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRM_REQUEST2 extends Model
{
    use HasFactory;
    use HasFactory;

    protected $table = 'crm_requests2';

    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'phoneno',
        'dob',
        'surname',
    ];
}
