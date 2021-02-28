<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tsubject extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'teacher',
        'subject',
    ];

}
