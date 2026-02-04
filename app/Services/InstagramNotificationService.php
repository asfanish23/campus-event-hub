<?php

namespace App\Services;

use App\Models\Event;
use App\Models\InstagramActivityNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class InstagramNotificationService
{
    /**
     * Define engagement milestones (likes + comments)
     */
    private const ENGAGEMENT_MILESTONES = [10, 25, 50, 100, 250, 500, 1000];

    /**
     * Define reach milestones
     */
    private const REACH_MILESTONES = [50, 100, 250, 500, 1000, 2500, 5000];

    /**
     * Check and create notifications for engagement milestones
     */
    public function checkEngagementMilestones(Event $event, array $previousMetrics = []): void
    {
        try {
            $currentEngagement = $event->getInstagramEngagement();
            $previousEngagement = $previousMetrics['engagement'] ?? 0;

            // Check each milestone
            foreach (self::ENGAGEMENT_MILESTONES as $milestone) {
                // If we've just crossed this milestone
                if ($previousEngagement < $milestone && $currentEngagement >= $milestone) {
                    $this->createMilestoneNotification(
                        $event,
                        'engagement_milestone',
                        $milestone,
                        "🎉 Your post reached {$milestone} total interactions (likes + comments)!"
                    );

                    Log::info('Created engagement milestone notification', [
                        'event_id' => $event->id,
                        'milestone' => $milestone,
                        'current_engagement' => $currentEngagement,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Exception in checkEngagementMilestones', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check and create notifications for reach milestones
     */
    public function checkReachMilestones(Event $event, array $previousMetrics = []): void
    {
        try {
            $currentReach = $event->instagram_reach;
            $previousReach = $previousMetrics['reach'] ?? 0;

            // Check each milestone
            foreach (self::REACH_MILESTONES as $milestone) {
                // If we've just crossed this milestone
                if ($previousReach < $milestone && $currentReach >= $milestone) {
                    $this->createMilestoneNotification(
                        $event,
                        'reach_milestone',
                        $milestone,
                        "📈 Your post has reached {$milestone} people on Instagram!"
                    );

                    Log::info('Created reach milestone notification', [
                        'event_id' => $event->id,
                        'milestone' => $milestone,
                        'current_reach' => $currentReach,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Exception in checkReachMilestones', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a post created notification
     */
    public function createPostNotification(Event $event): void
    {
        try {
            InstagramActivityNotification::create([
                'event_id' => $event->id,
                'club_id' => $event->club_id,
                'activity_type' => 'post_created',
                'message' => "✨ Your event \"{$event->name}\" was successfully posted to Instagram!",
            ]);

            Log::info('Created post notification', [
                'event_id' => $event->id,
                'club_id' => $event->club_id,
            ]);
        } catch (Exception $e) {
            Log::error('Exception in createPostNotification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a metrics sync notification
     */
    public function createSyncNotification(Event $event): void
    {
        try {
            InstagramActivityNotification::create([
                'event_id' => $event->id,
                'club_id' => $event->club_id,
                'activity_type' => 'sync_complete',
                'message' => "✅ Instagram metrics for \"{$event->name}\" have been updated! " .
                           "Engagement: {$event->getInstagramEngagement()} | " .
                           "Reach: {$event->instagram_reach} people",
            ]);

            Log::info('Created sync notification', [
                'event_id' => $event->id,
                'club_id' => $event->club_id,
            ]);
        } catch (Exception $e) {
            Log::error('Exception in createSyncNotification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a milestone notification
     */
    private function createMilestoneNotification(
        Event $event,
        string $activityType,
        int $milestoneValue,
        string $message
    ): void {
        try {
            // Check if notification already exists for this milestone
            $existingNotification = InstagramActivityNotification::where([
                'event_id' => $event->id,
                'activity_type' => $activityType,
                'milestone_value' => $milestoneValue,
            ])->exists();

            if ($existingNotification) {
                Log::info('Milestone notification already exists', [
                    'event_id' => $event->id,
                    'milestone_value' => $milestoneValue,
                ]);

                return;
            }

            InstagramActivityNotification::create([
                'event_id' => $event->id,
                'club_id' => $event->club_id,
                'activity_type' => $activityType,
                'milestone_value' => $milestoneValue,
                'milestone_label' => "{$milestoneValue}",
                'message' => $message,
            ]);

            Log::info('Created milestone notification', [
                'event_id' => $event->id,
                'milestone_value' => $milestoneValue,
            ]);
        } catch (Exception $e) {
            Log::error('Exception in createMilestoneNotification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get unread notifications for a club
     */
    public function getUnreadNotifications($clubId): array
    {
        try {
            $notifications = InstagramActivityNotification::where('club_id', $clubId)
                ->where('read', false)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return $notifications->toArray();
        } catch (Exception $e) {
            Log::error('Exception in getUnreadNotifications', [
                'club_id' => $clubId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get recent notifications for a club (read and unread)
     */
    public function getRecentNotifications($clubId, int $limit = 20): array
    {
        try {
            $notifications = InstagramActivityNotification::where('club_id', $clubId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $notifications->toArray();
        } catch (Exception $e) {
            Log::error('Exception in getRecentNotifications', [
                'club_id' => $clubId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get unread notification count for a club
     */
    public function getUnreadCount($clubId): int
    {
        try {
            return InstagramActivityNotification::where('club_id', $clubId)
                ->where('read', false)
                ->count();
        } catch (Exception $e) {
            Log::error('Exception in getUnreadCount', [
                'club_id' => $clubId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
