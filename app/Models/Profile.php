<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'nationality',
        'profile_type',
        'availability_on_demand',
        'per_hour',
    ];

    protected $casts = [
        'availability_on_demand' => 'boolean',
    ];

    const SELLER = 0;

    const BUYER = 1;

    const FEMALE = 0;

    const MALE = 1;

    const GENDERS = [
        self::FEMALE,
        self::MALE,
    ];

    const TYPES = [
        self::SELLER,
        self::BUYER,
    ];

    const GENDER_NAMES = [
        self::FEMALE => 'Female',
        self::MALE => 'Male',
    ];

    const TYPE_NAMES = [
        self::SELLER => 'Seller',
        self::BUYER => 'Buyer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute()
    {
        return self::TYPE_NAMES[$this->profile_type];
    }

    public function getGenderNameAttribute()
    {
        return self::GENDER_NAMES[$this->gender];
    }
}