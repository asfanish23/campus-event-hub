<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'phone',
        'bio',
        'profile_photo',
        'address',
        'city',
        'country',
        'postal_code',
        'club_id',
        'admin_status',
        'admin_application_reason',
        'admin_submitted_at',
        'telegram_chat_id',
        'telegram_connected',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'admin_submitted_at' => 'datetime',
    ];

    // Relationships
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function registrations()
    {
        return $this->hasMany(StudentEventRegistration::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function likes()
    {
        return $this->hasMany(EventLike::class);
    }

    public function likedEvents()
    {
        return $this->belongsToMany(Event::class, 'liked_events', 'user_id', 'event_id');
    }

    public function telegramPreference()
    {
        return $this->hasOne(TelegramUserPreference::class);
    }
}
