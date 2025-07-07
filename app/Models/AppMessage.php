<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppMessage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'group', // ATTENDANCE, FINANCE, LIB, ACADEMIC ETC
        'initiator',
        'message',
        'subject',
        'recipient_phone',
        'approved',
        'send'
    ];
}
