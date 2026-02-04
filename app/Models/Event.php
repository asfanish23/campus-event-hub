<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'category',
        'status',
        'expected_attendees',
        'event_image',
        'qr_active',
        'instagram_media_id',
        'instagram_posted_at',
        'instagram_last_synced_at',
        'instagram_likes_count',
        'instagram_comments_count',
        'instagram_reach',
        'instagram_impressions',
        'instagram_engagement_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'qr_active' => 'boolean',
        'instagram_posted_at' => 'datetime',
        'instagram_last_synced_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function media()
    {
        return $this->hasMany(EventMedia::class)->orderBy('order');
    }

    public function likes()
    {
        return $this->hasMany(EventLike::class);
    }

    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'event_likes', 'event_id', 'user_id')->withTimestamps();
    }

    /**
     * Check if event has been posted to Instagram
     */
    public function isPostedToInstagram(): bool
    {
        return !is_null($this->instagram_media_id);
    }

    /**
     * Get total Instagram engagement (likes + comments)
     */
    public function getInstagramEngagement(): int
    {
        return $this->instagram_likes_count + $this->instagram_comments_count;
    }

    /**
     * Check if Instagram metrics need to be synced (not synced in last hour)
     */
    public function needsInstagramSync(): bool
    {
        if (!$this->isPostedToInstagram()) {
            return false;
        }

        // Sync if never synced or last sync was more than 1 hour ago
        return is_null($this->instagram_last_synced_at) || 
               $this->instagram_last_synced_at->diffInMinutes(now()) >= 60;
    }

    /**
     * Check if event is liked by a specific user
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Get like percentage (useful for recommendations)
     */
    public function getLikePercentage(): float
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) {
            return 0;
        }
        return ($this->likes()->count() / $totalUsers) * 100;
    }
}

