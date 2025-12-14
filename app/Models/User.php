<?php

// namespace App\Models;

// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Auth\Authenticatable;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class User extends Model implements AuthenticatableContract
// {
//     use HasFactory, Authenticatable;

//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//         'role'
//     ];

//     protected $hidden = [
//         'password',
//     ];
//       public function isAdmin()
//     {
//         return $this->role === 'admin';
//     }

//     public function isStaff()
//     {
//         return $this->role === 'staff';
//     }
// }


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}

