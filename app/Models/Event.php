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
        'instagram_auto_post',
        'instagram_scheduled_at',
        'instagram_scheduled_posted',
        'instagram_auto_repost',
        'instagram_repost_at',
        'instagram_reposted',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'qr_active' => 'boolean',
        'instagram_posted_at' => 'datetime',
        'instagram_last_synced_at' => 'datetime',
        'instagram_auto_post' => 'boolean',
        'instagram_scheduled_at' => 'datetime',
        'instagram_scheduled_posted' => 'boolean',
        'instagram_auto_repost' => 'boolean',
        'instagram_repost_at' => 'datetime',
        'instagram_reposted' => 'boolean',
        'instagram_scheduled_at' => 'datetime',
        'instagram_scheduled_posted' => 'boolean',
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
        return $this->belongsToMany(User::class, 'liked_events', 'event_id', 'user_id');
    }

    public function registrations()
    {
        return $this->hasMany(StudentEventRegistration::class);
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
     * Get the computed/actual status of the event based on current date/time.
     * Returns: 'upcoming', 'ongoing', or 'completed'
     * 
     * This is independent of the database status field - it computes the real status.
     */
    public function getComputedStatus(): string
    {
        $now = now();
        $eventDate = $this->date; // Carbon date object
        $startTime = $this->start_time; // Carbon time object
        $endTime = $this->end_time; // Carbon time object

        // If event date is in the future, it's upcoming
        if ($eventDate->toDateString() > $now->toDateString()) {
            return 'upcoming';
        }

        // If event date is in the past, it's completed
        if ($eventDate->toDateString() < $now->toDateString()) {
            return 'completed';
        }

        // Event is today - check times
        if ($eventDate->toDateString() === $now->toDateString()) {
            // Create full datetime by combining date with times
            $startDateTime = $eventDate->copy()->setTimeFromTimeString($startTime->format('H:i:s'));
            $endDateTime = $eventDate->copy()->setTimeFromTimeString($endTime->format('H:i:s'));

            if ($now < $startDateTime) {
                return 'upcoming';
            } elseif ($now >= $startDateTime && $now < $endDateTime) {
                return 'ongoing';
            } else {
                return 'completed';
            }
        }

        return 'upcoming';
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

    /**
     * Check if event has Instagram auto-post enabled and is ready to post
     */
    public function isReadyForInstagramAutoPost(): bool
    {
        return $this->instagram_auto_post && 
               !$this->isPostedToInstagram() &&
               !is_null($this->event_image);
    }

    /**
     * Check if event has a scheduled Instagram post that needs to be sent
     */
    public function isReadyForScheduledInstagramPost(): bool
    {
        return $this->instagram_auto_post &&
               !is_null($this->instagram_scheduled_at) &&
               !$this->instagram_scheduled_posted &&
               !$this->isPostedToInstagram() &&
               $this->instagram_scheduled_at->isPast() &&
               !is_null($this->event_image);
    }

    /**
     * Get all events ready for Instagram scheduled posting
     */
    public static function getScheduledPostsReady()
    {
        return self::where('instagram_auto_post', true)
            ->where('instagram_scheduled_posted', false)
            ->whereNotNull('instagram_scheduled_at')
            ->whereNull('instagram_media_id')
            ->where('instagram_scheduled_at', '<=', now())
            ->get();
    }
}

