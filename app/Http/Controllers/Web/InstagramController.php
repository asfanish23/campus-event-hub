<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\InstagramService;
use App\Services\ImgBBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class InstagramController extends Controller
{
    private InstagramService $instagramService;
    private ImgBBService $imgbbService;

    public function __construct(InstagramService $instagramService, ImgBBService $imgbbService)
    {
        $this->instagramService = $instagramService;
        $this->imgbbService = $imgbbService;
    }

    /**
     * Display Instagram management dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Start query for events
        $query = Event::where('club_id', $user->club_id);
        
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
        
        // Filter by Instagram posting status
        if ($request->filled('instagram_status') && $request->get('instagram_status') !== '') {
            $instagramStatus = $request->get('instagram_status');
            if ($instagramStatus === 'posted') {
                $query->whereNotNull('instagram_media_id');
            } elseif ($instagramStatus === 'not_posted') {
                $query->whereNull('instagram_media_id');
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
        
        // Get Instagram credentials status
        $hasCredentials = config('services.instagram.token') && config('services.instagram.user_id');
        
        return view('instagram.index', compact('events', 'hasCredentials', 'categories'));
    }

    /**
     * Manually post an event to Instagram
     */
    public function postEvent(Request $request, Event $event)
    {
        Log::info('postEvent() method called', [
            'event_id' => $event->id,
            'event_name' => $event->name,
        ]);
        
        // Validate the event has an image
        if (!$event->event_image) {
            Log::warning('Event has no image', ['event_id' => $event->id]);
            return redirect()->back()->with('error', 'Event must have an image to post to Instagram');
        }

        try {
            // Get local image path
            $localImagePath = storage_path('app/public/' . $event->event_image);
            
            Log::info('Preparing to post event to Instagram', [
                'event_id' => $event->id,
                'local_path' => $localImagePath,
                'file_exists' => file_exists($localImagePath),
            ]);

            // Upload image to ImgBB to get a public URL
            $publicImageUrl = $this->imgbbService->uploadImage($localImagePath, 'event-' . $event->id);
            
            Log::info('Image uploaded to ImgBB', [
                'event_id' => $event->id,
                'imgbb_url' => $publicImageUrl,
            ]);

            // Create caption WITHOUT emojis
            $caption = $event->name . "\n\n" . 
                      $event->date->format('M d, Y') . "\n" . 
                      $event->location . "\n\n" . 
                      $event->description;
            
            // Post to Instagram using ImgBB URL
            $response = $this->instagramService->postImage($publicImageUrl, $caption);
            
            if ($response['success']) {
                Log::info('Event successfully posted to Instagram', [
                    'event_id' => $event->id,
                    'media_id' => $response['media_id']
                ]);
                
                return redirect()->back()->with('success', 'Event posted to Instagram successfully!');
            } else {
                Log::warning('Failed to post event to Instagram', [
                    'event_id' => $event->id,
                    'error' => $response['message']
                ]);
                
                return redirect()->back()->with('error', 'Failed to post to Instagram: ' . $response['message']);
            }
        } catch (Exception $e) {
            Log::error('Exception posting to Instagram', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Schedule an event to be posted to Instagram
     */
    public function scheduleEvent(Request $request, Event $event)
    {
        // Validate input
        $validated = $request->validate([
            'instagram_scheduled_at' => 'required|date_format:Y-m-d\TH:i|after:now',
        ], [
            'instagram_scheduled_at.required' => 'Please select a date and time',
            'instagram_scheduled_at.date_format' => 'Invalid date/time format',
            'instagram_scheduled_at.after' => 'Scheduled time must be in the future',
        ]);

        // Validate the event has an image
        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to schedule Instagram posting');
        }

        try {
            // Convert datetime-local format to timestamp
            $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['instagram_scheduled_at']);

            // Update event with scheduling info
            $event->update([
                'instagram_auto_post' => true,
                'instagram_scheduled_at' => $scheduledAt,
                'instagram_scheduled_posted' => false,
            ]);

            Log::info('Event scheduled for Instagram posting', [
                'event_id' => $event->id,
                'scheduled_at' => $scheduledAt,
            ]);

            return redirect()->back()->with('success', 'Event scheduled to post on Instagram at ' . $scheduledAt->format('M d, Y H:i') . '!');
        } catch (\Exception $e) {
            Log::error('Error scheduling Instagram post', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled Instagram post
     */
    public function cancelScheduledPost(Event $event)
    {
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
     * Show settings page
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
     * Test Instagram API with a public image
     */
    /**
     * Repost an event immediately to Instagram
     */
    public function repostNow(Request $request, Event $event)
    {
        // Validate the event has an image
        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to repost to Instagram');
        }

        // Validate event has been posted before
        if (!$event->isPostedToInstagram()) {
            return redirect()->back()->with('error', 'Event has not been posted to Instagram yet');
        }

        try {
            // Get local image path
            $localImagePath = storage_path('app/public/' . $event->event_image);
            
            Log::info('Preparing to repost event to Instagram', [
                'event_id' => $event->id,
                'local_path' => $localImagePath,
            ]);

            // Upload image to ImgBB to get a public URL
            $publicImageUrl = $this->imgbbService->uploadImage($localImagePath, 'event-repost-' . $event->id);
            
            Log::info('Image uploaded to ImgBB for repost', [
                'event_id' => $event->id,
                'imgbb_url' => $publicImageUrl,
            ]);

            // Create caption
            $caption = "🔄 REPOST\n\n" . 
                      $event->name . "\n\n" . 
                      $event->date->format('M d, Y') . "\n" . 
                      $event->location . "\n\n" . 
                      $event->description;
            
            // Post to Instagram using ImgBB URL
            $response = $this->instagramService->postImage($publicImageUrl, $caption);
            
            if ($response['success']) {
                Log::info('Event successfully reposted to Instagram', [
                    'event_id' => $event->id,
                    'media_id' => $response['media_id']
                ]);
                
                return redirect()->back()->with('success', 'Event reposted to Instagram successfully!');
            } else {
                Log::warning('Failed to repost event to Instagram', [
                    'event_id' => $event->id,
                    'error' => $response['message']
                ]);
                
                return redirect()->back()->with('error', 'Failed to repost to Instagram: ' . $response['message']);
            }
        } catch (Exception $e) {
            Log::error('Exception reposting to Instagram', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Schedule a repost of an event to Instagram
     */
    public function scheduleRepost(Request $request, Event $event)
    {
        // Validate input
        $validated = $request->validate([
            'instagram_repost_at' => 'required|date_format:Y-m-d\TH:i|after:now',
        ], [
            'instagram_repost_at.required' => 'Please select a date and time',
            'instagram_repost_at.date_format' => 'Invalid date/time format',
            'instagram_repost_at.after' => 'Scheduled time must be in the future',
        ]);

        // Validate the event has an image
        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to schedule reposting');
        }

        // Validate event has been posted before
        if (!$event->isPostedToInstagram()) {
            return redirect()->back()->with('error', 'Event has not been posted to Instagram yet');
        }

        try {
            // Convert datetime-local format to timestamp
            $repostAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['instagram_repost_at']);

            // Save repost schedule
            $event->update([
                'instagram_auto_repost' => true,
                'instagram_repost_at' => $repostAt,
                'instagram_reposted' => false,
            ]);

            Log::info('Event scheduled for Instagram reposting', [
                'event_id' => $event->id,
                'repost_at' => $repostAt,
            ]);

            return redirect()->back()->with('success', 'Event scheduled to repost on Instagram at ' . $repostAt->format('M d, Y H:i') . '!');
        } catch (\Exception $e) {
            Log::error('Error scheduling Instagram repost', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled Instagram repost
     */
    public function cancelRepostSchedule(Event $event)
    {
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
