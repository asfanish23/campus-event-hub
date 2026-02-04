<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class InstagramService
{
    /**
     * Instagram Graph API base URL
     */
    private const API_URL = 'https://graph.instagram.com/v18.0';

    /**
     * Instagram User ID from config
     */
    private string $igUserId;

    /**
     * Instagram Access Token from config
     */
    private string $accessToken;

    /**
     * Constructor - Initialize with credentials from config
     */
    public function __construct()
    {
        $this->igUserId = config('services.instagram.user_id');
        $this->accessToken = config('services.instagram.token');

        // Validate that credentials are configured
        if (!$this->igUserId || !$this->accessToken) {
            throw new Exception('Instagram credentials not configured in config/services.php');
        }
    }

    /**
     * Post an image to Instagram with caption
     *
     * This method handles the two-step process required by Instagram Graph API:
     * 1. Create a media container with the image URL and caption
     * 2. Publish the media container to make it visible on the Instagram feed
     *
     * @param string $imageUrl The public URL of the image to post
     * @param string $caption The caption text for the image
     * 
     * @return array Response containing success status and media ID
     * @throws Exception If the API requests fail
     */
    public function postImage(string $imageUrl, string $caption): array
    {
        try {
            Log::info('Instagram post request initiated', [
                'original_url' => $imageUrl,
                'caption_length' => strlen($caption),
            ]);

            // Step 1: Create a media container
            $mediaContainerId = $this->createMediaContainer($imageUrl, $caption);

            // Wait for Instagram to process the media container
            sleep(3);

            // Step 2: Publish the media container to make it visible
            $mediaId = $this->publishMedia($mediaContainerId);

            // Log successful post
            Log::info('Instagram post created successfully', [
                'media_id' => $mediaId,
                'ig_user_id' => $this->igUserId,
            ]);

            return [
                'success' => true,
                'message' => 'Image posted to Instagram successfully',
                'media_id' => $mediaId,
            ];
        } catch (Exception $e) {
            // Log the error with details
            Log::error('Failed to post to Instagram', [
                'error' => $e->getMessage(),
                'ig_user_id' => $this->igUserId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to post image to Instagram: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }

    /**
     * Post an image to Instagram with custom credentials
     *
     * This method is used for club-specific Instagram accounts where
     * credentials are stored in the database rather than config.
     *
     * @param string $imageUrl The public URL of the image to post
     * @param string $caption The caption text for the image
     * @param string $customAccessToken The access token for this specific account
     * @param string $customBusinessId The Instagram business ID for this specific account
     * 
     * @return array Response containing success status and media ID
     */
    public function postImageWithCustomCredentials(
        string $imageUrl,
        string $caption,
        string $customAccessToken,
        string $customBusinessId
    ): array {
        try {
            Log::info('Instagram post with custom credentials initiated', [
                'original_url' => $imageUrl,
                'caption_length' => strlen($caption),
                'business_id' => $customBusinessId,
            ]);

            // Step 1: Create a media container with custom credentials
            $mediaContainerId = $this->createMediaContainerCustom($imageUrl, $caption, $customAccessToken, $customBusinessId);

            // Wait for Instagram to process the media container
            sleep(3);

            // Step 2: Publish the media container
            $mediaId = $this->publishMediaCustom($mediaContainerId, $customAccessToken, $customBusinessId);

            Log::info('Instagram post with custom credentials created successfully', [
                'media_id' => $mediaId,
                'business_id' => $customBusinessId,
            ]);

            return [
                'success' => true,
                'message' => 'Image posted to Instagram successfully',
                'media_id' => $mediaId,
            ];
        } catch (Exception $e) {
            Log::error('Failed to post to Instagram with custom credentials', [
                'error' => $e->getMessage(),
                'business_id' => $customBusinessId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to post image to Instagram: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }

    /**
     * Step 1: Create a media container with the image
     *
     * Uses the image_url parameter with a public HTTPS URL.
     * Instagram will download the image from this URL.
     *
     * @param string $imageUrl Public HTTPS URL of the image
     * @param string $caption Caption for the image
     * 
     * @return string The media container ID
     * @throws Exception If the API request fails
     */
    private function createMediaContainer(string $imageUrl, string $caption): string
    {
        $endpoint = "{$this->igUserId}/media";
        $url = self::API_URL . "/{$endpoint}";

        // Ensure URL is HTTPS and public
        $imageUrl = str_replace('http://', 'https://', $imageUrl);
        
        // If it's a localhost URL, try to convert to public
        if (strpos($imageUrl, 'localhost') !== false || strpos($imageUrl, '127.0.0.1') !== false) {
            $ngrokUrl = env('NGROK_URL') ?? env('APP_URL');
            $imageUrl = str_replace(
                ['https://localhost:8000', 'https://127.0.0.1:8000', 'http://localhost:8000', 'http://127.0.0.1:8000'],
                $ngrokUrl,
                $imageUrl
            );
            Log::info('Converted localhost URL to public ngrok URL', ['image_url' => $imageUrl]);
        }

        Log::info('Instagram Media Container Request', [
            'api_url' => $url,
            'image_url' => $imageUrl,
            'caption_length' => strlen($caption),
            'caption_sample' => substr($caption, 0, 50),
        ]);

        // Make POST request to create media container
        $response = Http::post($url, [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $this->accessToken,
        ]);

        // Log the full response
        Log::info('Instagram Media Container Response', [
            'status' => $response->status(),
            'image_url_sent' => $imageUrl,
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();
            $code = $errorData['code'] ?? 'unknown';
            $subcode = $errorData['error_subcode'] ?? 'N/A';
            
            Log::error('Instagram API Error', [
                'error_message' => $error,
                'error_code' => $code,
                'error_subcode' => $subcode,
                'image_url' => $imageUrl,
                'full_response' => $response->json(),
            ]);
            
            throw new Exception("Instagram API Error: {$error}");
        }

        // Extract and return the media container ID
        $mediaContainerId = $response->json('id');
        
        if (!$mediaContainerId) {
            Log::error('No media container ID returned', [
                'response' => $response->json(),
            ]);
            throw new Exception('Media container ID not returned from Instagram API');
        }

        Log::info('Media container created', ['container_id' => $mediaContainerId]);

        return $mediaContainerId;
    }

    /**
     * Step 2: Publish the media container
     *
     * Takes the media container ID from step 1 and publishes it to Instagram
     *
     * @param string $mediaContainerId The ID from the media container
     * 
     * @return string The published media ID
     * @throws Exception If the API request fails
     */
    private function publishMedia(string $mediaContainerId): string
    {
        $endpoint = "{$this->igUserId}/media_publish";
        $url = self::API_URL . "/{$endpoint}";

        Log::info('Publishing media container', [
            'container_id' => $mediaContainerId,
            'api_url' => $url,
        ]);

        // Wait a moment for Instagram to process the media
        // Instagram sometimes needs time to process the image before it can be published
        sleep(3);

        // Make POST request to publish the media
        $response = Http::post($url, [
            'creation_id' => $mediaContainerId,
            'access_token' => $this->accessToken,
        ]);

        // Log the response
        Log::info('Instagram Media Publish Response', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();
            
            Log::error('Instagram Publish Error', [
                'error_message' => $error,
                'container_id' => $mediaContainerId,
                'full_response' => $response->json(),
            ]);
            
            throw new Exception("Failed to publish media: {$error}");
        }

        // Extract and return the media ID
        $mediaId = $response->json('id');
        
        if (!$mediaId) {
            throw new Exception('Media ID not returned from Instagram publish API');
        }

        Log::info('Media published successfully', ['media_id' => $mediaId]);

        return $mediaId;
    }

    /**
     * Create media container with custom credentials
     */
    private function createMediaContainerCustom(
        string $imageUrl,
        string $caption,
        string $accessToken,
        string $businessId
    ): string {
        $endpoint = "{$businessId}/media";
        $url = self::API_URL . "/{$endpoint}";

        // Ensure URL is HTTPS and public
        $imageUrl = str_replace('http://', 'https://', $imageUrl);
        
        Log::info('Instagram Media Container Request (Custom Credentials)', [
            'api_url' => $url,
            'image_url' => $imageUrl,
            'caption_length' => strlen($caption),
        ]);

        // Make POST request to create media container
        $response = Http::post($url, [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $accessToken,
        ]);

        Log::info('Instagram Media Container Response (Custom Credentials)', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();
            
            Log::error('Instagram API Error (Custom Credentials)', [
                'error_message' => $error,
                'full_response' => $response->json(),
            ]);
            
            throw new Exception("Instagram API Error: {$error}");
        }

        // Extract and return the media container ID
        $mediaContainerId = $response->json('id');
        
        if (!$mediaContainerId) {
            throw new Exception('Media container ID not returned from Instagram API');
        }

        Log::info('Media container created (Custom Credentials)', ['container_id' => $mediaContainerId]);

        return $mediaContainerId;
    }

    /**
     * Publish media container with custom credentials
     */
    private function publishMediaCustom(
        string $mediaContainerId,
        string $accessToken,
        string $businessId
    ): string {
        $endpoint = "{$businessId}/media_publish";
        $url = self::API_URL . "/{$endpoint}";

        Log::info('Publishing media container (Custom Credentials)', [
            'container_id' => $mediaContainerId,
            'api_url' => $url,
        ]);

        // Wait a moment for Instagram to process the media
        sleep(3);

        // Make POST request to publish the media
        $response = Http::post($url, [
            'creation_id' => $mediaContainerId,
            'access_token' => $accessToken,
        ]);

        Log::info('Instagram Media Publish Response (Custom Credentials)', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();
            
            Log::error('Instagram Publish Error (Custom Credentials)', [
                'error_message' => $error,
                'container_id' => $mediaContainerId,
                'full_response' => $response->json(),
            ]);
            
            throw new Exception("Failed to publish media: {$error}");
        }

        // Extract and return the media ID
        $mediaId = $response->json('id');
        
        if (!$mediaId) {
            throw new Exception('Media ID not returned from Instagram publish API');
        }

        Log::info('Media published successfully (Custom Credentials)', ['media_id' => $mediaId]);

        return $mediaId;
    }

    /**
     * Fetch insights for a media post
     * 
     * Gets engagement metrics like likes, comments, reach, and impressions
     * 
     * @param string $mediaId The Instagram media ID
     * @param string|null $accessToken Optional custom access token
     * 
     * @return array Insights data with likes, comments, reach, impressions
     */
    public function getMediaInsights(string $mediaId, ?string $accessToken = null): array
    {
        try {
            $token = $accessToken ?? $this->accessToken;
            $fields = 'likes_count,comments_count,reach,impressions';
            $url = self::API_URL . "/{$mediaId}/insights";

            Log::info('Fetching Instagram media insights', [
                'media_id' => $mediaId,
                'fields' => $fields,
            ]);

            $response = Http::get($url, [
                'fields' => $fields,
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json('error') ?? [];
                $error = $errorData['message'] ?? $response->body();
                
                Log::warning('Failed to fetch Instagram insights', [
                    'media_id' => $mediaId,
                    'error' => $error,
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                ];
            }

            $insights = $response->json('data') ?? [];
            $formattedInsights = [
                'likes_count' => 0,
                'comments_count' => 0,
                'reach' => 0,
                'impressions' => 0,
            ];

            // Map insights data
            foreach ($insights as $insight) {
                $name = $insight['name'] ?? '';
                $value = $insight['values'][0]['value'] ?? 0;
                
                if ($name === 'likes_count') {
                    $formattedInsights['likes_count'] = $value;
                } elseif ($name === 'comments_count') {
                    $formattedInsights['comments_count'] = $value;
                } elseif ($name === 'reach') {
                    $formattedInsights['reach'] = $value;
                } elseif ($name === 'impressions') {
                    $formattedInsights['impressions'] = $value;
                }
            }

            // Calculate engagement rate
            $totalEngagement = $formattedInsights['likes_count'] + $formattedInsights['comments_count'];
            $formattedInsights['engagement_rate'] = $formattedInsights['impressions'] > 0 
                ? round(($totalEngagement / $formattedInsights['impressions']) * 100, 2)
                : 0;

            Log::info('Instagram insights fetched successfully', [
                'media_id' => $mediaId,
                'insights' => $formattedInsights,
            ]);

            return [
                'success' => true,
                'data' => $formattedInsights,
            ];
        } catch (Exception $e) {
            Log::error('Exception while fetching Instagram insights', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

