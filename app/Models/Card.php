<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'card_number',
        'expiry_date',
        'cvc',
    ];
}
