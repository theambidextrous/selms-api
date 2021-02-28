<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'fname',
        'lname',
        'address',
        'city',
        'county',
        'zip',
        'email',
        'phone',
        'nok_fname',
        'nok_lname',
        'nok_email',
        'nok_phone',
        'join_date',
    ];
}
