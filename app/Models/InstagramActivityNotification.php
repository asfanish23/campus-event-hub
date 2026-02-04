<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramActivityNotification extends Model
{
    use HasFactory;

    protected $table = 'instagram_activity_notifications';

    protected $fillable = [
        'event_id',
        'club_id',
        'activity_type',
        'milestone_value',
        'milestone_label',
        'message',
        'read',
        'read_at',
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship to Club
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }

        return $this;
    }

    /**
     * Get human-readable activity type
     */
    public function getActivityTypeLabel(): string
    {
        return match($this->activity_type) {
            'post_created' => '📷 Post Created',
            'engagement_milestone' => '🎉 Engagement Milestone',
            'reach_milestone' => '📈 Reach Milestone',
            'sync_complete' => '✅ Metrics Updated',
            default => 'Activity',
        };
    }

    /**
     * Get icon for activity type
     */
    public function getActivityIcon(): string
    {
        return match($this->activity_type) {
            'post_created' => '📷',
            'engagement_milestone' => '🎉',
            'reach_milestone' => '📈',
            'sync_complete' => '✅',
            default => '📱',
        };
    }
}
