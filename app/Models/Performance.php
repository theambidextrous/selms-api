<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student',
        'subject',
        'group',
        'mark',
        'grade',
        'remark',
        'term',
    ];

}
