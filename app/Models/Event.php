<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
     * Relationship: Event has many social media post records.
     */
    public function socialPosts()
    {
        return $this->hasMany(\App\Models\SocialPost::class);
    }

    /**
     * Get latest social post record for a platform.
     */
    public function latestSocialPost(string $platform): ?\App\Models\SocialPost
    {
        if ($this->relationLoaded('socialPosts')) {
            return $this->socialPosts
                ->where('platform', $platform)
                ->sortByDesc(function (\App\Models\SocialPost $post) {
                    return optional($post->posted_at)->timestamp ?? 0;
                })
                ->first();
        }

        return $this->socialPosts()
            ->where('platform', $platform)
            ->latest('posted_at')
            ->latest('id')
            ->first();
    }

    /**
     * Check if event has been posted on a given platform.
     */
    public function isPostedToPlatform(string $platform): bool
    {
        $latest = $this->latestSocialPost($platform);

        return $latest?->status === \App\Models\SocialPost::STATUS_POSTED;
    }

    /**
     * Get last posted datetime for a platform.
     */
    public function postedAtForPlatform(string $platform): ?Carbon
    {
        $latest = $this->latestSocialPost($platform);

        if ($latest?->status === \App\Models\SocialPost::STATUS_POSTED) {
            return $latest->posted_at;
        }

        return null;
    }

    /**
     * Check if event has been posted to Instagram
     */
    public function isPostedToInstagram(): bool
    {
        return $this->isPostedToPlatform(\App\Models\SocialPost::PLATFORM_INSTAGRAM) || !is_null($this->instagram_media_id);
    }

    /**
     * Check if event has been posted to Facebook.
     */
    public function isPostedToFacebook(): bool
    {
        return $this->isPostedToPlatform(\App\Models\SocialPost::PLATFORM_FACEBOOK);
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

        if (!$eventDate) {
            return 'upcoming';
        }

        if (!$startTime || !$endTime) {
            return $eventDate->isPast() ? 'completed' : 'upcoming';
        }

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
     * Apply a computed-status filter using the event date and times.
     */
    public function scopeWhereComputedStatus(Builder $query, string $status): Builder
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        return match (strtolower(trim($status))) {
            'upcoming' => $query->where(function (Builder $builder) use ($today, $currentTime) {
                $builder->whereDate('date', '>', $today)
                    ->orWhere(function (Builder $nested) use ($today, $currentTime) {
                        $nested->whereDate('date', $today)
                            ->whereTime('start_time', '>', $currentTime);
                    });
            }),
            'currently running', 'ongoing' => $query->whereDate('date', $today)
                ->whereTime('start_time', '<=', $currentTime)
                ->whereTime('end_time', '>', $currentTime),
            'completed' => $query->where(function (Builder $builder) use ($today, $currentTime) {
                $builder->whereDate('date', '<', $today)
                    ->orWhere(function (Builder $nested) use ($today, $currentTime) {
                        $nested->whereDate('date', $today)
                            ->whereTime('end_time', '<=', $currentTime);
                    });
            }),
            default => $query,
        };
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

