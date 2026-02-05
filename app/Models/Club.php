<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'category',
        'founded_date',
        'description',
        'president_name',
        'president_contact',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'total_members',
        'profile_photo',
        'background_photo',
        'background_position_v',
    ];

    protected $casts = [
        'founded_date' => 'date',
    ];

    /**
     * Relationship: Club has many events
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Relationship: Club has one Instagram account
     */
    public function instagramAccount(): HasOne
    {
        return $this->hasOne(InstagramAccount::class);
    }

    /**
     * Relationship: Club has many products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}


