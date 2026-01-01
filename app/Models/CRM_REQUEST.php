<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRM_REQUEST extends Model
{
    use HasFactory;

    protected $table = 'crm_requests';

    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'bms_ticket_no',
        'ticket_no',
    ];
}
