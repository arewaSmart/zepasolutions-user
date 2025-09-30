<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelUpload extends Model
{
    protected $fillable = [
    'record_number',
    'beneficiary_account',
    'beneficiary_bankcode',
    'beneficiary_name',
    'transaction_amount',
    'narration',
    'new_account_name',
    'status',
];

}
