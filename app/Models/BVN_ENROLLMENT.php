<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BVN_ENROLLMENT extends Model
{
    use HasFactory;

    protected $table = 'bvn_enrollments';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'tnx_id',
        'refno',
        'username',
        'fullname',
        'email',
        'phone_number',
        'state',
        'lga',
        'address',
        'type',
        'bank_name',
        'account_number',
        'bvn',
        'account_name',
    ];
}
