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
    public function index()
    {
        $user = Auth::user();
        // Get only events from the club admin's club
        $events = Event::where('club_id', $user->club_id)->orderBy('created_at', 'desc')->get();
        
        // Get Instagram credentials status
        $hasCredentials = config('services.instagram.token') && config('services.instagram.user_id');
        
        return view('instagram.index', compact('events', 'hasCredentials'));
    }

    /**
     * Manually post an event to Instagram
     */
    public function postEvent(Request $request, Event $event)
    {
        // Validate the event has an image
        if (!$event->event_image) {
            return redirect()->back()->with('error', 'Event must have an image to post to Instagram');
        }

        try {
            // Get local image path
            $localImagePath = storage_path('app/public/' . $event->event_image);
            
            Log::info('Preparing to post event to Instagram', [
                'event_id' => $event->id,
                'local_path' => $localImagePath,
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
