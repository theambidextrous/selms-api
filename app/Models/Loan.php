<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member',
        'amount',
        'installment',
        'interest',
        'repayment_start',
        'repayment_end',
        'guarantors',
        'approved_by',
        'is_paid',
        'is_defaulted',
        'is_written_off',
    ];
}
