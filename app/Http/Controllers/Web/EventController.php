<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Club;
use App\Services\ClubInstagramService;
use App\Services\InstagramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    private ClubInstagramService $clubInstagramService;
    private InstagramNotificationService $notificationService;

    public function __construct(
        ClubInstagramService $clubInstagramService,
        InstagramNotificationService $notificationService
    ) {
        $this->clubInstagramService = $clubInstagramService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Event::query()->where('club_id', $user->club_id);

        // Filter by status
        if ($request->get('status') && $request->get('status') !== '') {
            $query->where('status', $request->get('status'));
        }

        // Filter by category
        if ($request->get('category') && $request->get('category') !== '') {
            $query->where('category', $request->get('category'));
        }

        // Filter by year
        if ($request->get('year') && $request->get('year') !== '') {
            $query->whereYear('date', $request->get('year'));
        }

        // Search by name
        if ($request->get('search')) {
            $query->where('name', 'like', '%' . $request->get('search') . '%');
        }

        $events = $query->orderBy('date', 'desc')->get();
        $categories = [
            'Academic',
            'Sports',
            'Culture',
            'Technology',
            'Volunteer',
            'Leadership',
            'Religious',
            'Entrepreneurship',
            'Arts & Media',
            'Others'
        ];
        
        return view('event.index', compact('events', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('event.create');
    }

    /**
     * Store a newly created resource in storage.
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

        // Assign club_id from authenticated user
        $user = Auth::user();
        $validated['club_id'] = $user->club_id;

        if ($request->hasFile('event_image')) {
            try {
                $imagePath = $request->file('event_image')->store('event-images', 'public');
                $validated['event_image'] = $imagePath;
            } catch (\Exception $e) {
                \Log::error('Event image upload error: ' . $e->getMessage());
                return back()->withErrors(['event_image' => 'Failed to upload image: ' . $e->getMessage()]);
            }
        }

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

        // Post event image to Instagram if available
        if (!empty($validated['event_image'])) {
            try {
                // Get the club associated with the current user
                // Assuming the user has a club or is part of a club
                $user = Auth::user();
                $club = Club::where('admin_id', $user->id)->first();
                
                if ($club) {
                    // Create caption
                    $caption = $event->name . "\n\n" . 
                              $event->date->format('M d, Y') . "\n" . 
                              $event->location . "\n\n" . 
                              $event->description;
                    
                    // Get the local image path
                    $localImagePath = storage_path('app/public/' . $validated['event_image']);
                    
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
                        ]);

                        // Create notification for post
                        $this->notificationService->createPostNotification($event);

                        Log::info('Event posted to club Instagram', [
                            'event_id' => $event->id,
                            'club_id' => $club->id,
                            'media_id' => $response['media_id']
                        ]);
                    } else {
                        Log::warning('Club Instagram posting failed', [
                            'event_id' => $event->id,
                            'club_id' => $club->id,
                            'error' => $response['message']
                        ]);
                    }
                } else {
                    Log::warning('No club found for user', ['user_id' => $user->id]);
                }
            } catch (\Exception $e) {
                Log::error('Exception during Instagram posting', ['event_id' => $event->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('event.index')->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('media', 'attendances', 'reviews');
        return view('event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('event.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
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
            'photos_to_delete' => 'nullable|array',
            'photos_to_delete.*' => 'integer|exists:event_media,id',
        ]);

        if ($request->hasFile('event_image')) {
            // Delete old image if it exists
            if ($event->event_image) {
                $oldPath = public_path('storage/' . $event->event_image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $imagePath = $request->file('event_image')->store('event-images', 'public');
            $validated['event_image'] = $imagePath;
        }

        $event->update($validated);

        // Delete photos marked for deletion
        if (!empty($validated['photos_to_delete'])) {
            foreach ($validated['photos_to_delete'] as $mediaId) {
                $media = EventMedia::find($mediaId);
                if ($media) {
                    Storage::disk('public')->delete($media->file_path);
                    $media->delete();
                }
            }
        }

        // Handle multiple photo uploads
        if ($request->hasFile('event_photos')) {
            $maxOrder = $event->media()->max('order') ?? -1;
            foreach ($request->file('event_photos') as $index => $photo) {
                $photoPath = $photo->store('event-media', 'public');
                EventMedia::create([
                    'event_id' => $event->id,
                    'file_path' => $photoPath,
                    'file_type' => 'image',
                    'order' => $maxOrder + $index + 1,
                ]);
            }
        }

        return redirect()->route('event.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Delete image if it exists
        if ($event->event_image) {
            $imagePath = public_path('storage/' . $event->event_image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $event->delete();

        return redirect()->route('event.index')->with('success', 'Event deleted successfully!');
    }

    /**
     * Show attendance management for event
     */
    public function attendance(Event $event)
    {
        // Get registrations from StudentEventRegistration
        $registrations = \App\Models\StudentEventRegistration::where('event_id', $event->id)
            ->with('user')
            ->get();
        
        // Get attendance records
        $attendances = $event->attendances;
        
        return view('event.attendance', compact('event', 'registrations', 'attendances'));
    }

    /**
     * Show reviews for event
     */
    public function reviews(Event $event)
    {
        $query = $event->reviews();

        // Filter by rating
        if (request('rating')) {
            $query->where('rating', request('rating'));
        }

        // Search by reviewer name
        if (request('search')) {
            $query->where('reviewer_name', 'like', '%' . request('search') . '%');
        }

        $reviews = $query->get();
        return view('event.reviews', compact('event', 'reviews'));
    }

    /**
     * Delete event media file
     */
    public function deleteMedia(EventMedia $eventMedia)
    {
        // Check if user is authorized to delete
        if ($eventMedia->event->club_id != auth()->user()->club_id && auth()->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the file from storage
        if ($eventMedia->file_path && Storage::disk('public')->exists($eventMedia->file_path)) {
            Storage::disk('public')->delete($eventMedia->file_path);
        }

        $eventMedia->delete();
        return response()->json(['success' => true]);
    }}