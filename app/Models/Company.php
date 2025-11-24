<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

     
    protected $fillable = [
        'name',
        'email',
        'phone',
        'daily_limit',
        'price_per_wash',
        'status',
    ];


    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }


    public function washes()
    {
        return $this->hasMany(Wash::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBanned($query)
    {
        return $query->where('status', 'banned');
    }
}
