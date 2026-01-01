<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'idno',
        'type',
        'nin',
        'first_name',
        'middle_name',
        'last_name',
        'phoneno',
        'email',
        'dob',
        'gender',
        'enrollment_branch',
        'enrollment_bank',
        'registration_date',
        'address',
        'photo',
        'title',
        'state',
        'lga',
        'trackingId',
        'lga',
        'town',
        'residence_state',
        'residence_lga',
        'residence_town',
        'signature',
    ];
}
