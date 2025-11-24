<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration',
        'driver_name',
        'company_id',
        'banned',
        'custom_price',
        'override_limit',
    ];

   public function washes()
{
    return $this->hasMany(Wash::class);
}

public function company()
{
    return $this->belongsTo(Company::class);
}

}
