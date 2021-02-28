<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Librarycatalogue extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'available',
        'lent',
        'lost',
    ];

}
