<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'term',
        'narration',
        'student',
        'fee',
        'subject',
        'type',
        'cleared'
    ];

}
