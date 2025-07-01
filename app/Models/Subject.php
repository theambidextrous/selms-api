<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'form',
        'name',
        'label',
        'pass_mark',
        'max_score',
        'tution_fee',
    ];

}
