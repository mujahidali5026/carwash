<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Wash;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'vehicle_registration',
        'company_id',
        'discount'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function washes()
    {
        return $this->hasMany(Wash::class, 'registration', 'vehicle_registration');
    }
}
