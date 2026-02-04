<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\InstagramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Inject InstagramService via dependency injection
     * Laravel automatically resolves this from the service container
     */
    private InstagramService $instagramService;

    public function __construct(InstagramService $instagramService)
    {
        $this->instagramService = $instagramService;
    }

    // ... other methods ...

    /**
     * Store a newly created resource in storage.
     * 
     * When an event is created, this method:
     * 1. Validates all input data
     * 2. Saves the event to database
     * 3. Handles event image uploads
     * 4. Automatically posts the event image to Instagram
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Upcoming,Currently Running,Completed',
            'expected_attendees' => 'nullable|integer|min:0',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'event_photos' => 'nullable|array',
            'event_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Store the event image if provided
        if ($request->hasFile('event_image')) {
            $imagePath = $request->file('event_image')->store('event-images', 'public');
            $validated['event_image'] = $imagePath;
        }

        // Create the event in database
        $event = Event::create($validated);

        // Handle multiple photo uploads
        if ($request->hasFile('event_photos')) {
            foreach ($request->file('event_photos') as $index => $photo) {
                $photoPath = $photo->store('event-media', 'public');
                EventMedia::create([
                    'event_id' => $event->id,
                    'file_path' => $photoPath,
                    'file_type' => 'image',
                    'order' => $index,
                ]);
            }
        }

        // ========== INSTAGRAM POSTING LOGIC ==========
        // After event is created, automatically post to Instagram
        if ($validated['event_image']) {
            // Generate public URL for the uploaded image
            // This URL must be publicly accessible for Instagram API
            $imageUrl = asset('storage/' . $validated['event_image']);

            // Create a compelling Instagram caption
            $caption = $this->generateInstagramCaption($event);

            // Call the Instagram service to post the image
            $instagramResponse = $this->instagramService->postImage($imageUrl, $caption);

            // Handle the response (optional - log success/failure)
            if ($instagramResponse['success']) {
                // Store the Instagram media ID in your event model if needed
                // $event->update(['instagram_media_id' => $instagramResponse['media_id']]);
            } else {
                // Log the failure but don't block event creation
                // The event is already saved, Instagram posting is optional
                \Illuminate\Support\Facades\Log::warning(
                    'Instagram posting failed for event',
                    ['event_id' => $event->id, 'error' => $instagramResponse['message']]
                );
            }
        }
        // ========== END INSTAGRAM POSTING ==========

        return redirect()->route('event.index')->with('success', 'Event created successfully!');
    }

    /**
     * Generate an engaging Instagram caption for the event
     * 
     * Customize this based on your event data and marketing needs
     */
    private function generateInstagramCaption(Event $event): string
    {
        return "🎉 {$event->name}\n\n" .
               "📅 {$event->date->format('M d, Y')}\n" .
               "📍 {$event->location}\n\n" .
               "{$event->description}\n\n" .
               "#CampusEvent #EventAlert #CampusLife #{$event->category}";
    }
}
