<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Librarybook extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'number',
        'catalogue',
        'status',
        'lent_to',
        'lent_from',
        'lent_until',
    ];

}
