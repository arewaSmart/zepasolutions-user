<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $table = 'verifications';

   protected $fillable = [
    'reference',
    'user_id',
    'service_field_id',
    'service_id',
    'field_code',
    'field_name',
    'service_name',
    'service_type',
    'description',
    'amount',
    'firstname',
    'middlename',
    'surname',
    'gender',
    'birthdate',
    'birthstate',
    'birthlga',
    'birthcountry',
    'maritalstatus',
    'email',
    'telephoneno',
    'residence_address',
    'residence_state',
    'residence_lga',
    'residence_town',
    'religion',
    'employmentstatus',
    'educationallevel',
    'profession',
    'height',
    'title',
    'nin',
    'number_nin',
    'vnin',
    'photo_path',
    'signature_path',
    'trackingId',
    'userid',
    'nok_firstname',
    'nok_middlename',
    'nok_surname',
    'nok_address1',
    'nok_address2',
    'nok_lga',
    'nok_state',
    'nok_town',
    'nok_postalcode',
    'self_origin_state',
    'self_origin_lga',
    'self_origin_place',
    'registrationDate',
    'enrollmentBank',
    'enrollmentBranch',
    'watchListed',
    'levelOfAccount',
    'nationality',
    'nameOnCard',
    'phoneNumber2',
    'performed_by',
    'approved_by',
    'tax_id',
    'comment',
    'response_data',
    'modification_data',
    'transaction_id',
    'submission_date',
    'status',
    'idno',
];


    protected $casts = [
        'submission_date' => 'datetime',
        'response_data' => 'array',
        'modification_data' => 'array',
    ];

    // Relationships
    public function serviceField()
    {
        return $this->belongsTo(ServiceField::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors and Mutators for backward compatibility with camelCase properties
    public function getStateOfOriginAttribute()
    {
        return $this->attributes['self_origin_state'] ?? $this->attributes['stateOfOrigin'] ?? null;
    }

    public function setStateOfOriginAttribute($value)
    {
        $this->attributes['self_origin_state'] = $value;
        $this->attributes['stateOfOrigin'] = $value;
    }

    public function getSelfOriginStateAttribute()
    {
        return $this->attributes['self_origin_state'] ?? $this->attributes['stateOfOrigin'] ?? null;
    }

    public function setSelfOriginStateAttribute($value)
    {
        $this->attributes['self_origin_state'] = $value;
        $this->attributes['stateOfOrigin'] = $value;
    }

    public function getLgaOfOriginAttribute()
    {
        return $this->attributes['self_origin_lga'] ?? $this->attributes['lgaOfOrigin'] ?? null;
    }

    public function setLgaOfOriginAttribute($value)
    {
        $this->attributes['self_origin_lga'] = $value;
        $this->attributes['lgaOfOrigin'] = $value;
    }

    public function getSelfOriginLgaAttribute()
    {
        return $this->attributes['self_origin_lga'] ?? $this->attributes['lgaOfOrigin'] ?? null;
    }

    public function setSelfOriginLgaAttribute($value)
    {
        $this->attributes['self_origin_lga'] = $value;
        $this->attributes['lgaOfOrigin'] = $value;
    }

    public function getMaritalStatusAttribute()
    {
        return $this->attributes['maritalstatus'] ?? $this->attributes['maritalStatus'] ?? null;
    }

    public function setMaritalStatusAttribute($value)
    {
        $this->attributes['maritalstatus'] = $value;
        $this->attributes['maritalStatus'] = $value;
    }

    public function getStateOfResidenceAttribute()
    {
        return $this->attributes['residence_state'] ?? $this->attributes['stateOfResidence'] ?? null;
    }

    public function setStateOfResidenceAttribute($value)
    {
        $this->attributes['residence_state'] = $value;
        $this->attributes['stateOfResidence'] = $value;
    }

    public function getResidenceStateAttribute()
    {
        return $this->attributes['residence_state'] ?? $this->attributes['stateOfResidence'] ?? null;
    }

    public function setResidenceStateAttribute($value)
    {
        $this->attributes['residence_state'] = $value;
        $this->attributes['stateOfResidence'] = $value;
    }

    public function getLgaOfResidenceAttribute()
    {
        return $this->attributes['residence_lga'] ?? $this->attributes['lgaOfResidence'] ?? null;
    }

    public function setLgaOfResidenceAttribute($value)
    {
        $this->attributes['residence_lga'] = $value;
        $this->attributes['lgaOfResidence'] = $value;
    }

    public function getResidenceLgaAttribute()
    {
        return $this->attributes['residence_lga'] ?? $this->attributes['lgaOfResidence'] ?? null;
    }

    public function setResidenceLgaAttribute($value)
    {
        $this->attributes['residence_lga'] = $value;
        $this->attributes['lgaOfResidence'] = $value;
    }

    public function getResidentialAddressAttribute()
    {
        return $this->attributes['residence_address'] ?? $this->attributes['residentialAddress'] ?? null;
    }

    public function setResidentialAddressAttribute($value)
    {
        $this->attributes['residence_address'] = $value;
        $this->attributes['residentialAddress'] = $value;
    }

    public function getResidenceAddressAttribute()
    {
        return $this->attributes['residence_address'] ?? $this->attributes['residentialAddress'] ?? null;
    }

    public function setResidenceAddressAttribute($value)
    {
        $this->attributes['residence_address'] = $value;
        $this->attributes['residentialAddress'] = $value;
    }
}
