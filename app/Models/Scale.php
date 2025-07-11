<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scale extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'min_mark',
        'max_mark',
        'grade',
        'form',
    ];

}
