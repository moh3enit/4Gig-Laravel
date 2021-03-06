<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'nationality',
        'birth_date',
        'gender',
        'availability_on_demand',
        'per_hour',
        'category_id',
        'sub_category_id',
        'description',
        'video_presentation',
        'portfolio',
        'is_active',
        'order',
    ];

    protected $casts = [
        'availability_on_demand' => 'boolean',
        'is_active' => 'boolean',
    ];

    const PRESENTATION_COLLECTION_NAME = 'presentation';

    const PORTFOLIO_COLLECTION_NAME = 'portfolio';

    const ACTIVE = 1;

    const INACTIVE = 0;

    const ORDER_ACTIVE = 1;

    const ORDER_INACTIVE = 0;

    const FEMALE = 0;

    const MALE = 1;

    const GENDERS = [
        self::FEMALE,
        self::MALE,
    ];

    const GENDER_NAMES = [
        self::FEMALE => 'Female',
        self::MALE => 'Male',
    ];

    protected static function booted()
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->orderBy('order', 'desc');
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PRESENTATION_COLLECTION_NAME)
            ->singleFile();

        $this->addMediaCollection(self::PORTFOLIO_COLLECTION_NAME)
            ->singleFile();
    }

    public function getGenderNameAttribute()
    {
        return self::GENDER_NAMES[$this->gender];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function spoken_languages()
    {
        return $this->hasMany(SpokenLanguage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
