<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'current_term',
        'day',
        'date',
        'time',
        'stream',
        'subject',
        'teacher',
        'datetime',
    ];

}
