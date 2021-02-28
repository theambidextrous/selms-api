<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
  
    protected $fillable = [
        'fname',
        'lname',
        'address',
        'city',
        'county',
        'zip',
        'email',
        'phone',
        'password',
        'is_super',
        'is_admin',
        'is_lib',
        'is_fin',
        'is_teacher',
        'is_parent',
        'is_active',
        'pic',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

   
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
