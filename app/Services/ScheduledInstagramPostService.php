<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Club;
use Illuminate\Support\Facades\Log;

class ScheduledInstagramPostService
{
    private ClubInstagramService $clubInstagramService;
    private InstagramNotificationService $notificationService;
    private ImgBBService $imgbbService;

    public function __construct(
        ClubInstagramService $clubInstagramService,
        InstagramNotificationService $notificationService,
        ImgBBService $imgbbService
    ) {
        $this->clubInstagramService = $clubInstagramService;
        $this->notificationService = $notificationService;
        $this->imgbbService = $imgbbService;
    }

    /**
     * Process all scheduled Instagram posts that are ready to be posted
     */
    public function processScheduledPosts(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $readyEvents = Event::getScheduledPostsReady();

        Log::info('Processing scheduled Instagram posts', [
            'events_count' => $readyEvents->count()
        ]);

        foreach ($readyEvents as $event) {
            try {
                $result = $this->postScheduledEvent($event);
                
                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'event_id' => $event->id,
                        'error' => $result['message']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Error processing scheduled Instagram post', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Also process scheduled reposts
        $readyReposts = Event::where('instagram_auto_repost', true)
            ->where('instagram_reposted', false)
            ->whereNotNull('instagram_repost_at')
            ->where('instagram_repost_at', '<=', now())
            ->get();

        Log::info('Processing scheduled Instagram reposts', [
            'events_count' => $readyReposts->count()
        ]);

        foreach ($readyReposts as $event) {
            try {
                $result = $this->repostScheduledEvent($event);
                
                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'event_id' => $event->id,
                        'error' => $result['message']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Error processing scheduled Instagram repost', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Scheduled Instagram posts processing completed', $results);

        return $results;
    }

    /**
     * Post a single scheduled event to Instagram
     */
    public function postScheduledEvent(Event $event): array
    {
        try {
            // Validate event has required fields
            if (!$event->event_image) {
                return [
                    'success' => false,
                    'message' => 'Event has no image'
                ];
            }

            if (!$event->instagram_scheduled_at || !$event->instagram_scheduled_at->isPast()) {
                return [
                    'success' => false,
                    'message' => 'Scheduled time has not arrived yet'
                ];
            }

            if ($event->isPostedToInstagram()) {
                return [
                    'success' => false,
                    'message' => 'Event already posted to Instagram'
                ];
            }

            // Get the club associated with the event
            $club = $event->club;

            if (!$club) {
                return [
                    'success' => false,
                    'message' => 'Event club not found'
                ];
            }

            // Create caption
            $caption = $event->name . "\n\n" . 
                      $event->date->format('M d, Y') . "\n" . 
                      $event->location . "\n\n" . 
                      $event->description;
            
            // Get the local image path
            $localImagePath = storage_path('app/public/' . $event->event_image);

            // Verify image exists
            if (!file_exists($localImagePath)) {
                return [
                    'success' => false,
                    'message' => 'Event image file not found'
                ];
            }

            // Post to club's Instagram account
            $response = $this->clubInstagramService->postEventToClubInstagram(
                $club,
                $localImagePath,
                $caption,
                (string)$event->id
            );

            if ($response['success']) {
                // Save Instagram media ID and timestamp to event
                $event->update([
                    'instagram_media_id' => $response['media_id'],
                    'instagram_posted_at' => now(),
                    'instagram_last_synced_at' => now(),
                    'instagram_scheduled_posted' => true,
                ]);

                // Create notification for post
                $this->notificationService->createPostNotification($event);

                Log::info('Scheduled event successfully posted to Instagram', [
                    'event_id' => $event->id,
                    'club_id' => $club->id,
                    'media_id' => $response['media_id'],
                    'scheduled_at' => $event->instagram_scheduled_at,
                ]);

                return [
                    'success' => true,
                    'message' => 'Event posted successfully',
                    'media_id' => $response['media_id']
                ];
            } else {
                Log::warning('Failed to post scheduled event to Instagram', [
                    'event_id' => $event->id,
                    'club_id' => $club->id,
                    'error' => $response['message']
                ]);

                return [
                    'success' => false,
                    'message' => 'Instagram posting failed: ' . $response['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during scheduled Instagram posting', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get status of a scheduled post
     */
    public function getScheduledPostStatus(Event $event): array
    {
        return [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'is_scheduled' => !is_null($event->instagram_scheduled_at),
            'scheduled_at' => $event->instagram_scheduled_at,
            'is_posted' => $event->isPostedToInstagram(),
            'is_ready' => $event->isReadyForScheduledInstagramPost(),
            'posted_at' => $event->instagram_posted_at,
            'scheduled_posted' => $event->instagram_scheduled_posted,
        ];
    }

    /**
     * Post a single scheduled repost to Instagram
     */
    public function repostScheduledEvent(Event $event): array
    {
        try {
            // Validate event has required fields
            if (!$event->event_image) {
                return [
                    'success' => false,
                    'message' => 'Event has no image'
                ];
            }

            if (!$event->instagram_repost_at || !$event->instagram_repost_at->isPast()) {
                return [
                    'success' => false,
                    'message' => 'Repost time has not arrived yet'
                ];
            }

            // Get the club associated with the event
            $club = $club = $event->club;

            // Get local image path
            $localImagePath = storage_path('app/public/' . $event->event_image);

            if (!file_exists($localImagePath)) {
                return [
                    'success' => false,
                    'message' => 'Event image file not found'
                ];
            }

            // Create repost caption
            $caption = "🔄 REPOST\n\n" . 
                      $event->name . "\n\n" . 
                      $event->date->format('M d, Y') . "\n" . 
                      $event->location . "\n\n" . 
                      $event->description;

            // Upload image to ImgBB to get a public URL
            $publicImageUrl = $this->imgbbService->uploadImage($localImagePath, 'event-repost-scheduled-' . $event->id);

            // Post to Instagram using the global service
            $response = app(\App\Services\InstagramService::class)->postImage($publicImageUrl, $caption);

            if ($response['success']) {
                // Update event with repost info
                $event->update([
                    'instagram_auto_repost' => false,
                    'instagram_repost_at' => null,
                    'instagram_reposted' => true,
                ]);

                Log::info('Scheduled repost successfully posted to Instagram', [
                    'event_id' => $event->id,
                    'media_id' => $response['media_id'],
                    'repost_at' => $event->instagram_repost_at,
                ]);

                return [
                    'success' => true,
                    'message' => 'Repost successfully posted',
                    'media_id' => $response['media_id']
                ];
            } else {
                Log::warning('Failed to post scheduled repost to Instagram', [
                    'event_id' => $event->id,
                    'error' => $response['message']
                ]);

                return [
                    'success' => false,
                    'message' => 'Instagram posting failed: ' . $response['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during scheduled repost posting', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}
