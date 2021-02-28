<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'year',
        'label',
        'start',
        'end',
        'is_current',
        'f1_fee',
        'f2_fee',
        'f3_fee',
        'f4_fee',
    ];

}
