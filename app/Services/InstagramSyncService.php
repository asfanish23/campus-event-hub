<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Exception;

class InstagramSyncService
{
    private InstagramService $instagramService;
    private InstagramNotificationService $notificationService;

    public function __construct(
        InstagramService $instagramService,
        InstagramNotificationService $notificationService
    ) {
        $this->instagramService = $instagramService;
        $this->notificationService = $notificationService;
    }

    /**
     * Sync Instagram metrics for all events that need syncing
     * 
     * Fetches the latest engagement metrics for events that have been posted
     * to Instagram and updates the database.
     * 
     * @return array Results of the sync operation
     */
    public function syncAllEventMetrics(): array
    {
        try {
            Log::info('Starting Instagram metrics sync for all events');

            // Get all events that have Instagram posts and need syncing
            $eventsToSync = Event::where('instagram_media_id', '!=', null)
                ->where(function ($query) {
                    $query->whereNull('instagram_last_synced_at')
                        ->orWhereRaw('instagram_last_synced_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
                })
                ->get();

            $results = [
                'total_synced' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
            ];

            foreach ($eventsToSync as $event) {
                $syncResult = $this->syncEventMetrics($event);
                
                $results['total_synced']++;
                
                if ($syncResult['success']) {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'error' => $syncResult['error'],
                    ];
                }
            }

            Log::info('Completed Instagram metrics sync', $results);

            return $results;
        } catch (Exception $e) {
            Log::error('Exception in syncAllEventMetrics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_synced' => 0,
                'successful' => 0,
                'failed' => 1,
                'errors' => [['error' => $e->getMessage()]],
            ];
        }
    }

    /**
     * Sync metrics for a specific event
     * 
     * Fetches the latest Instagram engagement metrics for a single event
     * 
     * @param Event $event The event to sync
     * @return array Result with success status and data or error message
     */
    public function syncEventMetrics(Event $event): array
    {
        try {
            // Check if event has Instagram media ID
            if (!$event->isPostedToInstagram()) {
                Log::warning('Attempted to sync metrics for event not posted to Instagram', [
                    'event_id' => $event->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'Event not posted to Instagram',
                ];
            }

            Log::info('Syncing Instagram metrics', [
                'event_id' => $event->id,
                'media_id' => $event->instagram_media_id,
            ]);

            // Store previous metrics for comparison
            $previousMetrics = [
                'engagement' => $event->getInstagramEngagement(),
                'reach' => $event->instagram_reach,
                'likes' => $event->instagram_likes_count,
                'comments' => $event->instagram_comments_count,
            ];

            // Fetch insights from Instagram
            $insightsResult = $this->instagramService->getMediaInsights(
                $event->instagram_media_id,
                $event->club->instagramAccount?->getDecryptedToken()
            );

            if (!$insightsResult['success']) {
                return [
                    'success' => false,
                    'error' => $insightsResult['error'] ?? 'Failed to fetch insights',
                ];
            }

            $insights = $insightsResult['data'];

            // Update event with new metrics
            $event->update([
                'instagram_likes_count' => $insights['likes_count'],
                'instagram_comments_count' => $insights['comments_count'],
                'instagram_reach' => $insights['reach'],
                'instagram_impressions' => $insights['impressions'],
                'instagram_engagement_rate' => $insights['engagement_rate'],
                'instagram_last_synced_at' => now(),
            ]);

            // Refresh event instance to get updated data
            $event->refresh();

            // Check for milestone achievements
            $this->notificationService->checkEngagementMilestones($event, $previousMetrics);
            $this->notificationService->checkReachMilestones($event, $previousMetrics);

            // Create sync notification
            $this->notificationService->createSyncNotification($event);

            Log::info('Successfully synced Instagram metrics', [
                'event_id' => $event->id,
                'metrics' => $insights,
            ]);

            return [
                'success' => true,
                'data' => $insights,
            ];
        } catch (Exception $e) {
            Log::error('Exception while syncing event metrics', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get sync status for all posted events
     * 
     * Returns a summary of Instagram posting and syncing status
     * 
     * @return array Status information
     */
    public function getSyncStatus(): array
    {
        try {
            $totalPostedEvents = Event::whereNotNull('instagram_media_id')->count();
            $recentlySyncedEvents = Event::whereNotNull('instagram_media_id')
                ->whereNotNull('instagram_last_synced_at')
                ->where('instagram_last_synced_at', '>=', now()->subHours(1))
                ->count();
            
            $needsSyncCount = Event::where('instagram_media_id', '!=', null)
                ->where(function ($query) {
                    $query->whereNull('instagram_last_synced_at')
                        ->orWhereRaw('instagram_last_synced_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
                })
                ->count();

            $topEngagedEvents = Event::whereNotNull('instagram_media_id')
                ->orderBy('instagram_engagement_rate', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'instagram_likes_count', 'instagram_comments_count', 'instagram_reach', 'instagram_engagement_rate']);

            return [
                'total_posted_events' => $totalPostedEvents,
                'recently_synced' => $recentlySyncedEvents,
                'needs_sync' => $needsSyncCount,
                'top_engaged_events' => $topEngagedEvents,
            ];
        } catch (Exception $e) {
            Log::error('Exception in getSyncStatus', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
