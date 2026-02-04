<?php

namespace App\Services;

use App\Models\Club;
use App\Models\InstagramAccount;
use Illuminate\Support\Facades\Log;
use Exception;

class ClubInstagramService
{
    private InstagramService $instagramService;
    private ImgBBService $imgbbService;

    public function __construct(InstagramService $instagramService, ImgBBService $imgbbService)
    {
        $this->instagramService = $instagramService;
        $this->imgbbService = $imgbbService;
    }

    /**
     * Post an event to the club's Instagram account
     *
     * @param Club $club The club that owns the event
     * @param string $localImagePath Path to the event image
     * @param string $caption Caption for the post
     * @param string $eventId Event ID for logging
     * 
     * @return array Response with success status and media ID
     */
    public function postEventToClubInstagram(Club $club, string $localImagePath, string $caption, string $eventId): array
    {
        try {
            // Check if club has Instagram account configured
            $instagramAccount = $club->instagramAccount;
            
            // TEMPORARY: If no club account, use first available account
            if (!$instagramAccount) {
                Log::info('Club has no Instagram account, using fallback account', [
                    'club_id' => $club->id,
                    'event_id' => $eventId,
                ]);
                
                // Get any available Instagram account (temporary solution)
                $instagramAccount = InstagramAccount::first();
                
                if (!$instagramAccount) {
                    Log::warning('No Instagram accounts available at all', [
                        'club_id' => $club->id,
                        'event_id' => $eventId,
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'No Instagram accounts available',
                        'media_id' => null,
                    ];
                }
            }

            // Check if token is still valid
            if (!$instagramAccount->isTokenValid()) {
                Log::warning('Club Instagram token is invalid or expired', [
                    'club_id' => $club->id,
                    'instagram_account_id' => $instagramAccount->id,
                    'event_id' => $eventId,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Instagram account token is invalid or expired',
                    'media_id' => null,
                ];
            }

            Log::info('Starting Instagram post for club', [
                'club_id' => $club->id,
                'event_id' => $eventId,
                'instagram_account_id' => $instagramAccount->id,
            ]);

            // Upload image to ImgBB
            $publicImageUrl = $this->imgbbService->uploadImage($localImagePath, "event-{$eventId}-{$club->id}");
            
            Log::info('Image uploaded to ImgBB for club event', [
                'club_id' => $club->id,
                'event_id' => $eventId,
                'imgbb_url' => $publicImageUrl,
            ]);

            // Use the club's specific Instagram account credentials
            $response = $this->instagramService->postImageWithCustomCredentials(
                $publicImageUrl,
                $caption,
                $instagramAccount->getDecryptedToken(),
                $instagramAccount->instagram_business_id
            );

            if ($response['success']) {
                // Update last post timestamp
                $instagramAccount->update(['last_post_at' => now()]);
                
                Log::info('Club event successfully posted to Instagram', [
                    'club_id' => $club->id,
                    'event_id' => $eventId,
                    'media_id' => $response['media_id'],
                ]);
                
                return $response;
            } else {
                Log::warning('Failed to post club event to Instagram', [
                    'club_id' => $club->id,
                    'event_id' => $eventId,
                    'error' => $response['message'],
                ]);
                
                return $response;
            }
        } catch (Exception $e) {
            Log::error('Exception posting club event to Instagram', [
                'club_id' => $club->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Error posting to Instagram: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }
}
