<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'admission',
        'date_of_admission',
        'fname',
        'lname',
        'address',
        'city',
        'county',
        'zip',
        'parent',
        'form',
        'stream',
        'kcpe',
        'current_term',
        'expected_grad',
        'gender',
        'dob',
        'birth_cert',
        'nemis_no',
        'huduma_no',
        'is_active',
        'pic',
    ];
}
