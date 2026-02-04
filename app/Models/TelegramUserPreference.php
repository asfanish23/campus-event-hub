<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_chat_id',
        'user_id',
        'category_preferences',
        'notifications_enabled',
        'notification_time',
        'days_in_advance',
        'send_event_updates',
        'send_club_news',
    ];

    protected $casts = [
        'category_preferences' => 'array',
        'notifications_enabled' => 'boolean',
        'send_event_updates' => 'boolean',
        'send_club_news' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
