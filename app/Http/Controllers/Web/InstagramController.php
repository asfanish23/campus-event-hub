<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SocialPost;
use App\Services\ClubActivityService;
use App\Services\InstagramService;
use App\Services\ImgBBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

class InstagramController extends Controller
{
    private InstagramService $instagramService;
    private ImgBBService $imgbbService;
    private ClubActivityService $clubActivityService;

    public function __construct(InstagramService $instagramService, ImgBBService $imgbbService, ClubActivityService $clubActivityService)
    {
        $this->instagramService = $instagramService;
        $this->imgbbService = $imgbbService;
        $this->clubActivityService = $clubActivityService;
    }

    /**
     * Display Social Media management dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $query = Event::where('club_id', $user->club_id)
            ->with(['socialPosts' => function ($q) {
                $q->latest('posted_at')->latest('id');
            }]);
        
        // Search by event name or location
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by status
        if ($request->filled('status') && $request->get('status') !== '') {
            $query->where('status', $request->get('status'));
        }
        
        // Filter by category
        if ($request->filled('category') && $request->get('category') !== '') {
            $query->where('category', $request->get('category'));
        }
        
        // Filter by Instagram posting status.
        if ($request->filled('instagram_status') && $request->get('instagram_status') !== '') {
            $instagramStatus = $request->get('instagram_status');
            if ($instagramStatus === 'posted') {
                $query->where(function ($q) {
                    $q->whereNotNull('instagram_media_id')
                        ->orWhereHas('socialPosts', function ($socialQuery) {
                            $socialQuery->where('platform', SocialPost::PLATFORM_INSTAGRAM)
                                ->where('status', SocialPost::STATUS_POSTED);
                        });
                });
            } elseif ($instagramStatus === 'not_posted') {
                $query->whereNull('instagram_media_id')
                    ->whereDoesntHave('socialPosts', function ($socialQuery) {
                        $socialQuery->where('platform', SocialPost::PLATFORM_INSTAGRAM)
                            ->where('status', SocialPost::STATUS_POSTED);
                    });
            } elseif ($instagramStatus === 'scheduled') {
                $query->where('instagram_auto_post', true)
                      ->whereNotNull('instagram_scheduled_at')
                      ->where('instagram_scheduled_posted', false);
            }
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'date_desc');
        switch ($sortBy) {
            case 'date_asc':
                $query->orderBy('date', 'asc');
                break;
            case 'date_desc':
                $query->orderBy('date', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('date', 'desc');
        }
        
        $events = $query->get();
        
        // Get unique categories for filter
        $categories = Event::where('club_id', $user->club_id)
            ->select('category')
            ->distinct()
            ->pluck('category');
        
        $hasInstagramCredentials = !empty(config('services.instagram.token')) && !empty(config('services.instagram.user_id'));
        $hasFacebookCredentials = !empty(config('services.facebook.page_access_token')) && !empty(config('services.facebook.page_id'));
        $hasCredentials = $hasInstagramCredentials;

        return view('instagram.index', compact(
            'events',
            'hasCredentials',
            'hasInstagramCredentials',
            'hasFacebookCredentials',
            'categories'
        ));
    }

    /**
     * Backward-compatible route handler.
     */
    public function postEvent(Request $request, Event $event)
    {
        $this->ensureOwnedEvent($event);
        return $this->postToInstagram($event->id);
    }

    /**
     * Post or repost to Instagram.
     */
    public function postToInstagram(int $eventId)
    {
        $event = $this->getOwnedEvent($eventId);

        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to post to Instagram');
        }

        if (empty(config('services.instagram.token')) || empty(config('services.instagram.user_id'))) {
            return redirect()->back()->with('error', 'Instagram credentials are not configured.');
        }

        $alreadyPosted = $event->isPostedToInstagram();
        $hadPendingSchedule = $this->hasPendingInstagramSchedule($event);

        try {
            $publicImageUrl = $this->makeEventImagePublicUrl($event, 'instagram');
            $caption = $this->buildCaption($event, $alreadyPosted);
            $response = $this->instagramService->postImage($publicImageUrl, $caption);

            if (!($response['success'] ?? false)) {
                $message = $response['message'] ?? 'Unknown Instagram API error';
                $this->recordSocialPost($event, SocialPost::PLATFORM_INSTAGRAM, SocialPost::STATUS_FAILED, null);

                return redirect()->back()->with('error', 'Failed to post to Instagram: ' . $message);
            }

            $postedAt = now();

            $event->update([
                'instagram_media_id' => $response['media_id'] ?? null,
                'instagram_posted_at' => $postedAt,
            ]);

            if ($hadPendingSchedule) {
                $this->clearInstagramScheduleState($event);
            }

            $instagramPermalink = $this->fetchInstagramPermalink($response['media_id'] ?? null);

            $this->recordSocialPost(
                $event,
                SocialPost::PLATFORM_INSTAGRAM,
                SocialPost::STATUS_POSTED,
                $response['media_id'] ?? null,
                $postedAt,
                $instagramPermalink
            );

            $actionText = $alreadyPosted ? 'reposted' : 'posted';

            return redirect()->back()->with('success', 'Event ' . $actionText . ' to Instagram successfully!');
        } catch (Exception $e) {
            Log::error('Instagram posting error', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordSocialPost($event, SocialPost::PLATFORM_INSTAGRAM, SocialPost::STATUS_FAILED, null);

            return redirect()->back()->with('error', 'Error posting to Instagram: ' . $e->getMessage());
        }
    }

    /**
     * Post or repost to Facebook Page.
     */
    public function postToFacebook(int $eventId)
    {
        $event = $this->getOwnedEvent($eventId);

        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to post to Facebook');
        }

        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.page_access_token');
        if (empty($pageId) || empty($accessToken)) {
            return redirect()->back()->with('error', 'Facebook credentials are not configured.');
        }

        $alreadyPosted = $event->isPostedToFacebook();

        try {
            $publicImageUrl = $this->makeEventImagePublicUrl($event, 'facebook');
            $caption = $this->buildCaption($event, $alreadyPosted);
            $response = $this->postImageToFacebook($pageId, $accessToken, $publicImageUrl, $caption);

            if (!($response['success'] ?? false)) {
                $message = $response['message'] ?? 'Unknown Facebook API error';
                $this->recordSocialPost($event, SocialPost::PLATFORM_FACEBOOK, SocialPost::STATUS_FAILED, null);

                return redirect()->back()->with('error', 'Failed to post to Facebook: ' . $message);
            }

            $postedAt = now();
            $this->recordSocialPost(
                $event,
                SocialPost::PLATFORM_FACEBOOK,
                SocialPost::STATUS_POSTED,
                $response['post_id'] ?? null,
                $postedAt
            );

            $actionText = $alreadyPosted ? 'reposted' : 'posted';

            return redirect()->back()->with('success', 'Event ' . $actionText . ' to Facebook successfully!');
        } catch (Exception $e) {
            Log::error('Facebook posting error', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordSocialPost($event, SocialPost::PLATFORM_FACEBOOK, SocialPost::STATUS_FAILED, null);

            return redirect()->back()->with('error', 'Error posting to Facebook: ' . $e->getMessage());
        }
    }

    /**
     * Publish or repost event to all currently supported platforms.
     */
    public function publishAllPlatforms(int $eventId)
    {
        $event = $this->getOwnedEvent($eventId);

        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to publish on all platforms');
        }

        $results = [];

        $results['instagram'] = $this->postToInstagramSilently($event);
        $results['facebook'] = $this->postToFacebookSilently($event);

        $successPlatforms = collect($results)
            ->filter(fn ($result) => $result['success'])
            ->keys()
            ->map(fn ($platform) => ucfirst($platform))
            ->implode(', ');

        $failedMessages = collect($results)
            ->filter(fn ($result) => !$result['success'])
            ->map(fn ($result, $platform) => ucfirst($platform) . ': ' . ($result['message'] ?? 'Failed'))
            ->implode(' | ');

        if ($successPlatforms === '') {
            return redirect()->back()->with('error', 'Publish failed on all platforms. ' . $failedMessages);
        }

        if ($failedMessages !== '') {
            return redirect()->back()->with('success', 'Published on: ' . $successPlatforms . '. Partial issues: ' . $failedMessages);
        }

        return redirect()->back()->with('success', 'Published successfully on all available platforms.');
    }

    private function postToInstagramSilently(Event $event): array
    {
        if (empty(config('services.instagram.token')) || empty(config('services.instagram.user_id'))) {
            return ['success' => false, 'message' => 'Instagram credentials are not configured'];
        }

        try {
            $hadPendingSchedule = $this->hasPendingInstagramSchedule($event);

            $publicImageUrl = $this->makeEventImagePublicUrl($event, 'instagram');
            $caption = $this->buildCaption($event, $event->isPostedToInstagram());
            $response = $this->instagramService->postImage($publicImageUrl, $caption);

            if (!($response['success'] ?? false)) {
                $message = $response['message'] ?? 'Unknown Instagram API error';
                $this->recordSocialPost($event, SocialPost::PLATFORM_INSTAGRAM, SocialPost::STATUS_FAILED, null);
                return ['success' => false, 'message' => $message];
            }

            $postedAt = now();
            $event->update([
                'instagram_media_id' => $response['media_id'] ?? null,
                'instagram_posted_at' => $postedAt,
            ]);

            if ($hadPendingSchedule) {
                $this->clearInstagramScheduleState($event);
            }

            $instagramPermalink = $this->fetchInstagramPermalink($response['media_id'] ?? null);

            $this->recordSocialPost(
                $event,
                SocialPost::PLATFORM_INSTAGRAM,
                SocialPost::STATUS_POSTED,
                $response['media_id'] ?? null,
                $postedAt,
                $instagramPermalink
            );

            return ['success' => true, 'message' => 'Posted to Instagram'];
        } catch (Exception $e) {
            Log::error('Silent Instagram publish failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordSocialPost($event, SocialPost::PLATFORM_INSTAGRAM, SocialPost::STATUS_FAILED, null);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function postToFacebookSilently(Event $event): array
    {
        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.page_access_token');

        if (empty($pageId) || empty($accessToken)) {
            return ['success' => false, 'message' => 'Facebook credentials are not configured'];
        }

        try {
            $publicImageUrl = $this->makeEventImagePublicUrl($event, 'facebook');
            $caption = $this->buildCaption($event, $event->isPostedToFacebook());
            $response = $this->postImageToFacebook($pageId, $accessToken, $publicImageUrl, $caption);

            if (!($response['success'] ?? false)) {
                $message = $response['message'] ?? 'Unknown Facebook API error';
                $this->recordSocialPost($event, SocialPost::PLATFORM_FACEBOOK, SocialPost::STATUS_FAILED, null);
                return ['success' => false, 'message' => $message];
            }

            $this->recordSocialPost(
                $event,
                SocialPost::PLATFORM_FACEBOOK,
                SocialPost::STATUS_POSTED,
                $response['post_id'] ?? null,
                now()
            );

            return ['success' => true, 'message' => 'Posted to Facebook'];
        } catch (Exception $e) {
            Log::error('Silent Facebook publish failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordSocialPost($event, SocialPost::PLATFORM_FACEBOOK, SocialPost::STATUS_FAILED, null);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function postImageToFacebook(string $pageId, string $accessToken, string $imageUrl, string $caption): array
    {
        $response = Http::asForm()->post("https://graph.facebook.com/v22.0/{$pageId}/photos", [
            'url' => $imageUrl,
            'caption' => $caption,
            'published' => 'true',
            'access_token' => $accessToken,
        ]);

        if (!$response->successful()) {
            $error = data_get($response->json(), 'error.message', $response->body());
            return ['success' => false, 'message' => $error];
        }

        return [
            'success' => true,
            'post_id' => data_get($response->json(), 'post_id') ?? data_get($response->json(), 'id'),
        ];
    }

    private function getOwnedEvent(int $eventId): Event
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        return Event::where('id', $eventId)
            ->where('club_id', $user->club_id)
            ->firstOrFail();
    }

    private function ensureOwnedEvent(Event $event): void
    {
        $user = Auth::user();
        if (!$user || $event->club_id !== $user->club_id) {
            abort(403);
        }
    }

    private function makeEventImagePublicUrl(Event $event, string $platform): string
    {
        $localImagePath = storage_path('app/public/' . $event->event_image);

        return $this->imgbbService->uploadImage($localImagePath, $platform . '-event-' . $event->id . '-' . now()->format('YmdHis'));
    }

    private function hasPendingInstagramSchedule(Event $event): bool
    {
        return (bool) $event->instagram_auto_post
            && !is_null($event->instagram_scheduled_at)
            && !$event->instagram_scheduled_posted;
    }

    private function clearInstagramScheduleState(Event $event): void
    {
        $event->update([
            'instagram_auto_post' => false,
            'instagram_scheduled_at' => null,
            'instagram_scheduled_posted' => true,
        ]);
    }

    private function fetchInstagramPermalink(?string $mediaId): ?string
    {
        if (empty($mediaId)) {
            return null;
        }

        $accessToken = config('services.instagram.token');
        if (empty($accessToken)) {
            return null;
        }

        try {
            // Primary attempt via Graph API endpoint.
            $response = Http::get("https://graph.facebook.com/v22.0/{$mediaId}", [
                'fields' => 'permalink',
                'access_token' => $accessToken,
            ]);

            if ($response->successful() && $response->json('permalink')) {
                return (string) $response->json('permalink');
            }

            // Fallback for Instagram graph host compatibility.
            $fallback = Http::get("https://graph.instagram.com/v18.0/{$mediaId}", [
                'fields' => 'permalink',
                'access_token' => $accessToken,
            ]);

            if ($fallback->successful() && $fallback->json('permalink')) {
                return (string) $fallback->json('permalink');
            }

            Log::warning('Instagram permalink unavailable', [
                'media_id' => $mediaId,
                'primary_status' => $response->status(),
                'fallback_status' => $fallback->status(),
            ]);
        } catch (Exception $e) {
            Log::warning('Instagram permalink fetch failed', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function buildCaption(Event $event, bool $isRepost = false): string
    {
        $prefix = $isRepost ? "REPOST\n\n" : '';

        return $prefix
            . $event->name . "\n\n"
            . $event->date->format('M d, Y') . "\n"
            . $event->location . "\n\n"
            . $event->description;
    }

    private function recordSocialPost(
        Event $event,
        string $platform,
        string $status,
        ?string $platformPostId,
        ?Carbon $postedAt = null,
        ?string $permalink = null
    ): SocialPost {
        return SocialPost::create([
            'event_id' => $event->id,
            'platform' => $platform,
            'platform_post_id' => $platformPostId,
            'permalink' => $permalink,
            'status' => $status,
            'posted_at' => $postedAt,
        ]);
    }

    /**
     * Schedule an event to be posted to Instagram.
     */
    public function scheduleEvent(Request $request, Event $event)
    {
        $this->ensureOwnedEvent($event);

        $validated = $request->validate([
            'instagram_scheduled_at' => 'required|date_format:Y-m-d\TH:i|after:now',
        ], [
            'instagram_scheduled_at.required' => 'Please select a date and time',
            'instagram_scheduled_at.date_format' => 'Invalid date/time format',
            'instagram_scheduled_at.after' => 'Scheduled time must be in the future',
        ]);

        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to schedule Instagram posting');
        }

        try {
            $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['instagram_scheduled_at']);

            $event->update([
                'instagram_auto_post' => true,
                'instagram_scheduled_at' => $scheduledAt,
                'instagram_scheduled_posted' => false,
            ]);

            return redirect()->back()->with('success', 'Event scheduled to post on Instagram at ' . $scheduledAt->format('M d, Y H:i') . '!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled Instagram post.
     */
    public function cancelScheduledPost(Event $event)
    {
        $this->ensureOwnedEvent($event);

        try {
            $event->update([
                'instagram_auto_post' => false,
                'instagram_scheduled_at' => null,
                'instagram_scheduled_posted' => false,
            ]);

            Log::info('Scheduled Instagram post cancelled', [
                'event_id' => $event->id,
            ]);

            return redirect()->back()->with('success', 'Scheduled post cancelled!');
        } catch (\Exception $e) {
            Log::error('Error cancelling scheduled post', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show settings page.
     */
    public function settings()
    {
        $hasToken = !empty(config('services.instagram.token'));
        $hasUserId = !empty(config('services.instagram.user_id'));
        $hasImgBBKey = !empty(env('IMGBB_API_KEY'));
        
        return view('instagram.settings', [
            'hasToken' => $hasToken,
            'hasUserId' => $hasUserId,
            'hasImgBBKey' => $hasImgBBKey,
        ]);
    }

    /**
     * Repost an event immediately to Instagram.
     */
    public function repostNow(Request $request, Event $event)
    {
        $this->ensureOwnedEvent($event);
        return $this->postToInstagram($event->id);
    }

    /**
     * Schedule a repost of an event to Instagram.
     */
    public function scheduleRepost(Request $request, Event $event)
    {
        $this->ensureOwnedEvent($event);

        $validated = $request->validate([
            'instagram_repost_at' => 'required|date_format:Y-m-d\TH:i|after:now',
        ], [
            'instagram_repost_at.required' => 'Please select a date and time',
            'instagram_repost_at.date_format' => 'Invalid date/time format',
            'instagram_repost_at.after' => 'Scheduled time must be in the future',
        ]);

        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to schedule reposting');
        }

        if (!$event->isPostedToInstagram()) {
            return redirect()->back()->with('error', 'Event has not been posted to Instagram yet');
        }

        try {
            $repostAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['instagram_repost_at']);

            $event->update([
                'instagram_auto_repost' => true,
                'instagram_repost_at' => $repostAt,
                'instagram_reposted' => false,
            ]);

            return redirect()->back()->with('success', 'Event scheduled to repost on Instagram at ' . $repostAt->format('M d, Y H:i') . '!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled Instagram repost.
     */
    public function cancelRepostSchedule(Event $event)
    {
        $this->ensureOwnedEvent($event);

        try {
            $event->update([
                'instagram_auto_repost' => false,
                'instagram_repost_at' => null,
                'instagram_reposted' => false,
            ]);

            Log::info('Scheduled Instagram repost cancelled', [
                'event_id' => $event->id,
            ]);

            return redirect()->back()->with('success', 'Scheduled repost cancelled!');
        } catch (\Exception $e) {
            Log::error('Error cancelling scheduled repost', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test Instagram API with a public image.
     */
    public function testApi()
    {
        try {
            // Use a proven public image URL that Instagram can access
            $testImageUrl = 'https://www.gstatic.com/images/icons/material/system/1x/home_white_24dp.png';
            
            Log::info('Testing Instagram API with public image', [
                'test_image_url' => $testImageUrl
            ]);
            
            $response = $this->instagramService->postImage($testImageUrl, 'Test Post - Campus Event Hub API Test');
            
            if ($response['success']) {
                return redirect()->back()->with('success', 'Test successful! Instagram API is working. Media ID: ' . $response['media_id']);
            } else {
                return redirect()->back()->with('error', 'Test failed: ' . $response['message']);
            }
        } catch (Exception $e) {
            Log::error('Instagram API test error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Test error: ' . $e->getMessage());
        }
    }
}
