<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'member',
        'position',
        'office_address',
        'mandate',
        'valid_until',
        'pay',
    ];
}
