<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentsport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student',
        'sport',
        'achievement',
    ];

}
