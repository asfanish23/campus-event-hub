<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    use HasFactory;

    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_THREADS = 'threads';

    public const STATUS_POSTED = 'posted';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'event_id',
        'platform',
        'platform_post_id',
        'permalink',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public static function platformValues(): array
    {
        return [
            self::PLATFORM_INSTAGRAM,
            self::PLATFORM_FACEBOOK,
            self::PLATFORM_THREADS,
        ];
    }

    public static function statusValues(): array
    {
        return [
            self::STATUS_POSTED,
            self::STATUS_FAILED,
            self::STATUS_PENDING,
        ];
    }
}
