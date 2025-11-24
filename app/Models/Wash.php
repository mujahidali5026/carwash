<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wash extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'customer_id',
        'amount',
        'is_cash',
        'registration',
        'signature',
        'approved_by',
    ];

    public function vehicle()
{
    return $this->belongsTo(Vehicle::class);
}


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
