<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ThreadsService
{
    /**
     * Threads Graph API base URL
     */
    private const API_URL = 'https://graph.threads.net/v1.0';

    /**
     * Threads User ID from config
     */
    private string $threadsUserId;

    /**
     * Threads Access Token from config
     */
    private string $accessToken;

    /**
     * Constructor - Initialize with credentials from config
     */
    public function __construct()
    {
        $this->threadsUserId = (string) config('services.threads.user_id', '');
        $this->accessToken = (string) config('services.threads.token', '');
    }

    /**
     * Check if global Threads credentials are configured
     */
    public function hasGlobalCredentials(): bool
    {
        return !empty($this->threadsUserId) && !empty($this->accessToken);
    }

    /**
     * Step 1: Create a Threads media container for an image post
     *
     * Threads requires a two-step workflow for image posts:
     * 1. Create a media container with the image URL and text
     * 2. Publish the container to make it visible on the Threads feed
     *
     * @param string $imageUrl The public URL of the image to post
     * @param string $text The text content for the post
     * @param string $accessToken The access token for this account
     * @param string $threadsUserId The Threads user ID for this account
     *
     * @return string The media container ID
     * @throws Exception If the API request fails
     */
    public function createThreadContainer(string $imageUrl, string $text, string $accessToken, string $threadsUserId): string
    {
        $endpoint = "{$threadsUserId}/threads";
        $url = self::API_URL . "/{$endpoint}";

        // Ensure URL is HTTPS and public
        $imageUrl = str_replace('http://', 'https://', $imageUrl);

        Log::info('Threads Media Container Request', [
            'api_url' => $url,
            'image_url' => $imageUrl,
            'text_length' => strlen($text),
            'text_sample' => substr($text, 0, 50),
        ]);

        // Make POST request to create media container
        $response = Http::post($url, [
            'media_type' => 'IMAGE',
            'image_url' => $imageUrl,
            'text' => $text,
            'access_token' => $accessToken,
        ]);

        // Log the full response
        Log::info('Threads Media Container Response', [
            'status' => $response->status(),
            'image_url_sent' => $imageUrl,
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();
            $code = $errorData['code'] ?? 'unknown';

            Log::error('Threads API Error', [
                'error_message' => $error,
                'error_code' => $code,
                'image_url' => $imageUrl,
                'full_response' => $response->json(),
            ]);

            throw new Exception("Threads API Error: {$error}");
        }

        // Extract and return the media container ID
        $mediaContainerId = $response->json('id');

        if (!$mediaContainerId) {
            Log::error('No media container ID returned', [
                'response' => $response->json(),
            ]);
            throw new Exception('Media container ID not returned from Threads API');
        }

        Log::info('Threads media container created', ['container_id' => $mediaContainerId]);

        return $mediaContainerId;
    }

    /**
     * Step 2: Publish a Threads media container
     *
     * Takes the media container ID from step 1 and publishes it to Threads
     *
     * @param string $containerId The ID from the media container
     * @param string $accessToken The access token for this account
     * @param string $threadsUserId The Threads user ID for this account
     *
     * @return string The published media ID
     * @throws Exception If the API request fails
     */
    public function publishThread(string $containerId, string $accessToken, string $threadsUserId): string
    {
        $endpoint = "{$threadsUserId}/threads_publish";
        $url = self::API_URL . "/{$endpoint}";

        Log::info('Publishing Threads media container', [
            'container_id' => $containerId,
            'api_url' => $url,
        ]);

        // Wait a moment for Threads to process the media
        // Threads sometimes needs time to process the image before it can be published
        sleep(3);

        // Make POST request to publish the media
        $response = Http::post($url, [
            'creation_id' => $containerId,
            'access_token' => $accessToken,
        ]);

        // Log the response
        Log::info('Threads Media Publish Response', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();

            Log::error('Threads Publish Error', [
                'error_message' => $error,
                'container_id' => $containerId,
                'full_response' => $response->json(),
            ]);

            throw new Exception("Failed to publish media: {$error}");
        }

        // Extract and return the media ID
        $mediaId = $response->json('id');

        if (!$mediaId) {
            throw new Exception('Media ID not returned from Threads publish API');
        }

        Log::info('Threads media published successfully', ['media_id' => $mediaId]);

        return $mediaId;
    }

    /**
     * Post an image to Threads with caption
     *
     * This method handles the two-step process required by Threads Graph API:
     * 1. Create a media container with the image URL and text
     * 2. Publish the media container to make it visible on the Threads feed
     *
     * Uses the global Threads credentials from config.
     *
     * @param string $imageUrl The public URL of the image to post
     * @param string $text The text content for the post
     *
     * @return array Response containing success status and media ID
     */
    public function postImage(string $imageUrl, string $text): array
    {
        if (!$this->hasGlobalCredentials()) {
            return [
                'success' => false,
                'message' => 'Threads credentials not configured in config/services.php',
                'media_id' => null,
            ];
        }

        try {
            Log::info('Threads post request initiated', [
                'original_url' => $imageUrl,
                'text_length' => strlen($text),
            ]);

            // Step 1: Create a media container
            $mediaContainerId = $this->createThreadContainer($imageUrl, $text, $this->accessToken, $this->threadsUserId);

            // Step 2: Publish the media container to make it visible
            $mediaId = $this->publishThread($mediaContainerId, $this->accessToken, $this->threadsUserId);

            // Log successful post
            Log::info('Threads post created successfully', [
                'media_id' => $mediaId,
                'threads_user_id' => $this->threadsUserId,
            ]);

            return [
                'success' => true,
                'message' => 'Image posted to Threads successfully',
                'media_id' => $mediaId,
            ];
        } catch (Exception $e) {
            // Log the error with details
            Log::error('Failed to post to Threads', [
                'error' => $e->getMessage(),
                'threads_user_id' => $this->threadsUserId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to post image to Threads: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }

    /**
     * Post an image to Threads with custom credentials
     *
     * This method is used for club-specific Threads accounts where
     * credentials are stored in the database rather than config.
     *
     * @param string $imageUrl The public URL of the image to post
     * @param string $text The text content for the post
     * @param string $customAccessToken The access token for this specific account
     * @param string $customThreadsUserId The Threads user ID for this specific account
     *
     * @return array Response containing success status and media ID
     */
    public function postImageWithCustomCredentials(
        string $imageUrl,
        string $text,
        string $customAccessToken,
        string $customThreadsUserId
    ): array {
        try {
            Log::info('Threads post with custom credentials initiated', [
                'original_url' => $imageUrl,
                'text_length' => strlen($text),
                'threads_user_id' => $customThreadsUserId,
            ]);

            // Step 1: Create a media container with custom credentials
            $mediaContainerId = $this->createThreadContainer($imageUrl, $text, $customAccessToken, $customThreadsUserId);

            // Wait for Threads to process the media container
            sleep(3);

            // Step 2: Publish the media container
            $mediaId = $this->publishThread($mediaContainerId, $customAccessToken, $customThreadsUserId);

            Log::info('Threads post with custom credentials created successfully', [
                'media_id' => $mediaId,
                'threads_user_id' => $customThreadsUserId,
            ]);

            return [
                'success' => true,
                'message' => 'Image posted to Threads successfully',
                'media_id' => $mediaId,
            ];
        } catch (Exception $e) {
            Log::error('Failed to post to Threads with custom credentials', [
                'error' => $e->getMessage(),
                'threads_user_id' => $customThreadsUserId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to post image to Threads: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }

    /**
     * Post a text-only update to Threads
     *
     * Text-only posts are published immediately and do not require the
     * two-step container/publish workflow used for image posts.
     *
     * Uses the global Threads credentials from config.
     *
     * @param string $text The text content for the post
     *
     * @return array Response containing success status and media ID
     */
    public function postText(string $text): array
    {
        if (!$this->hasGlobalCredentials()) {
            return [
                'success' => false,
                'message' => 'Threads credentials not configured in config/services.php',
                'media_id' => null,
            ];
        }

        return $this->postTextWithCustomCredentials($text, $this->accessToken, $this->threadsUserId);
    }

    /**
     * Post a text-only update to Threads with custom credentials
     *
     * Text-only posts are published immediately and do not require the
     * two-step container/publish workflow used for image posts.
     *
     * @param string $text The text content for the post
     * @param string $customAccessToken The access token for this specific account
     * @param string $customThreadsUserId The Threads user ID for this specific account
     *
     * @return array Response containing success status and media ID
     */
    public function postTextWithCustomCredentials(
        string $text,
        string $customAccessToken,
        string $customThreadsUserId
    ): array {
        try {
            $endpoint = "{$customThreadsUserId}/threads";
            $url = self::API_URL . "/{$endpoint}";

            Log::info('Threads text post request initiated', [
                'text_length' => strlen($text),
                'text_sample' => substr($text, 0, 50),
            ]);

            $response = Http::post($url, [
                'media_type' => 'TEXT',
                'text' => $text,
                'access_token' => $customAccessToken,
            ]);

            Log::info('Threads text post response', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            if (!$response->successful()) {
                $errorData = $response->json('error') ?? [];
                $error = $errorData['message'] ?? $response->body();

                Log::error('Threads text post error', [
                    'error_message' => $error,
                    'full_response' => $response->json(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to post text to Threads: ' . $error,
                    'media_id' => null,
                ];
            }

            $mediaId = $response->json('id');

            Log::info('Threads text post created successfully', [
                'media_id' => $mediaId,
                'threads_user_id' => $customThreadsUserId,
            ]);

            return [
                'success' => true,
                'message' => 'Text posted to Threads successfully',
                'media_id' => $mediaId,
            ];
        } catch (Exception $e) {
            Log::error('Exception posting text to Threads', [
                'error' => $e->getMessage(),
                'threads_user_id' => $customThreadsUserId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to post text to Threads: ' . $e->getMessage(),
                'media_id' => null,
            ];
        }
    }

    /**
     * Fetch the permalink for a published Threads media item
     *
     * @param string $mediaId The Threads media ID
     * @param string $accessToken The access token for this account
     *
     * @return string|null The permalink URL, or null if unavailable
     */
    public function getMediaPermalink(string $mediaId, string $accessToken): ?string
    {
        try {
            $url = self::API_URL . "/{$mediaId}";

            $response = Http::get($url, [
                'fields' => 'permalink',
                'access_token' => $accessToken,
            ]);

            if ($response->successful() && $response->json('permalink')) {
                return (string) $response->json('permalink');
            }

            Log::warning('Threads permalink unavailable', [
                'media_id' => $mediaId,
                'status' => $response->status(),
            ]);
        } catch (Exception $e) {
            Log::warning('Threads permalink fetch failed', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Refresh an unexpired long-lived Threads user access token.
     *
     * Long-lived tokens are valid for 60 days. They can be refreshed as long as
     * they are at least 24 hours old but have not yet expired. Refreshed tokens
     * are valid for 60 days from the date of refresh.
     *
     * Official docs: GET https://graph.threads.net/refresh_access_token
     *   ?grant_type=th_refresh_token&access_token=<LONG_LIVED_ACCESS_TOKEN>
     *
     * @param string $accessToken The valid (unexpired) long-lived access token
     *
     * @return array Response containing success status, new token and expiry
     */
    public function refreshLongLivedToken(string $accessToken): array
    {
        $response = Http::get('https://graph.threads.net/refresh_access_token', [
            'grant_type' => 'th_refresh_token',
            'access_token' => $accessToken,
        ]);

        if (!$response->successful()) {
            $errorData = $response->json('error') ?? [];
            $error = $errorData['message'] ?? $response->body();

            Log::error('Threads token refresh failed', [
                'status' => $response->status(),
                'error_message' => $error,
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to refresh Threads token: ' . $error,
                'access_token' => null,
                'expires_in' => null,
            ];
        }

        $refreshedToken = $response->json('access_token');

        if (!$refreshedToken) {
            Log::error('Threads token refresh response missing access_token', [
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Threads token refresh response did not include an access token.',
                'access_token' => null,
                'expires_in' => null,
            ];
        }

        Log::info('Threads token refreshed successfully', [
            'expires_in' => $response->json('expires_in'),
        ]);

        return [
            'success' => true,
            'message' => 'Threads token refreshed successfully',
            'access_token' => $refreshedToken,
            'expires_in' => $response->json('expires_in'),
        ];
    }
}
